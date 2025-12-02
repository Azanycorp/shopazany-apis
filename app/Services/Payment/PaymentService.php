<?php

namespace App\Services\Payment;

use App\Enum\PaymentType;
use App\Http\Resources\PaymentVerifyResource;
use App\Models\Bank;
use App\Models\PaymentService as ModelPaymentService;
use App\Services\Curl\GetCurl;
use App\Services\Curl\GetCurlService;
use App\Services\Payment\AuthorizeNet\ChargeCardService;
use App\Trait\HttpResponse;
use App\Trait\Transfer;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    use HttpResponse, Transfer;

    public function __construct(
        protected ChargeCardService $chargeCardService,
        private readonly AuthManager $authManager,
        private readonly Repository $cacheManager,
        private readonly ConfigRepository $repository,
        private readonly ResponseFactory $responseFactory
    ) {}

    public function processPayment($request)
    {
        $paymentProcessor = match ($request->payment_method) {
            PaymentType::PAYSTACK => new PaystackPaymentProcessor,
            PaymentType::B2B_PAYSTACK => new B2BPaystackPaymentProcessor,
            default => throw new \Exception('Unsupported payment method'),
        };

        $paymentService = new HandlePaymentService($paymentProcessor);

        $paymentDetails = match ($request->payment_method) {
            PaymentType::PAYSTACK => PaymentDetailsService::paystackPayDetails($request),
            PaymentType::B2B_PAYSTACK => PaymentDetailsService::b2bPaystackPayDetails($request),
            default => throw new \Exception('Unsupported payment method'),
        };

        return $paymentService->process($paymentDetails);
    }

    public function webhook($request)
    {
        $secretKey = $this->repository->get('paystack.secretKey');
        $signature = $request->header('x-paystack-signature');
        $payload = $request->getContent();

        if (! $signature || $signature !== hash_hmac('sha512', $payload, $secretKey)) {
            return $this->error(null, 'Invalid signature', 400);
        }

        $event = json_decode($payload, true);

        if (! isset($event['event']) || ! isset($event['data'])) {
            return $this->error(null, 'Invalid payload', 400);
        }

        PaystackEventHandler::handle($event);

        return $this->responseFactory->json(['status' => true], 200);
    }

    public function verifyPayment($userId, $ref)
    {
        $currentUserId = $this->authManager->id();
        if ($currentUserId != $userId) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        if ($ref === null || ! preg_match('/^[A-Za-z0-9]{10,30}$/', $ref)) {
            return $this->error(null, 'Invalid payment reference.', 400);
        }

        $verify = (new GetCurlService($ref))->run();
        $data = new PaymentVerifyResource($verify);

        return $this->success($data, 'Payment verify status');
    }

    public function approveTransfer($request)
    {
        $payload = json_decode($request->getContent(), true);

        $transfers = data_get($payload, 'data.transfers', []);

        if (blank($transfers)) {
            Log::warning('No transfers found in approval payload:', $payload);

            return $this->responseFactory->json(['message' => 'Invalid transfer request'], 400);
        }

        foreach ($transfers as $transfer) {
            $isValid = $this->isValidTransferRequest([
                'reference' => $transfer['reference'] ?? null,
                'amount' => $transfer['amount'] ?? null,
                'recipient' => $transfer['recipient']['recipientCode'] ?? null,
            ]);

            if (! $isValid) {
                return $this->responseFactory->json(['message' => 'Invalid transfer request'], 400);
            }
        }

        return $this->responseFactory->json(['message' => 'Transfer approved'], 200);
    }

    public function authorizeNetCard($request)
    {
        if ($request->type == 'b2b') {
            return $this->chargeCardService->processB2BPayment($request->all());
        }

        return $this->chargeCardService->processPayment($request->all());
    }

    public function getPaymentMethod($countryId)
    {
        $services = ModelPaymentService::whereHas('countries', function (Builder $q) use ($countryId): void {
            $q->where('country_id', $countryId);
        })->with('countries')->get();

        $data = $services->map(function ($service): array {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'slug' => $service->slug,
            ];
        });

        return $this->success($data, 'Payment methods');
    }

    public function getBanks()
    {
        $banks = $this->cacheManager->remember('banks_list', 43200, function () {
            $banks = Bank::select('id', 'name', 'slug', 'code')->get();

            if ($banks->isNotEmpty()) {
                return $banks;
            }

            return null;
        });

        if (blank($banks)) {
            return $this->error(null, 'No banks found', 404);
        }

        return $this->success($banks, 'Banks retrieved successfully');
    }

    public function accountLookUp($request): array
    {
        $url = $this->repository->get('services.paystack.bank_base_url').'/resolve?account_number='.$request->account_number.'&bank_code='.$request->bank_code;

        $token = $this->repository->get('services.paystack.test_sk');

        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ];

        return (new GetCurl($url, $headers))->execute();
    }
}
