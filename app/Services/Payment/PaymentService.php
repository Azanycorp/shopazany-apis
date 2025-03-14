<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Enum\PaymentType;
use App\Enum\PaystackEvent;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\Curl\GetCurlService;
use App\Http\Resources\PaymentVerifyResource;
use App\Models\Bank;
use App\Models\PaymentService as ModelPaymentService;
use App\Services\Curl\GetCurl;
use App\Services\Payment\AuthorizeNet\ChargeCardService;
use Illuminate\Support\Facades\Cache;
use App\Services\Payment\HandlePaymentService;
use App\Services\Payment\PaymentDetailsService;
use App\Services\Payment\PaystackPaymentProcessor;
use App\Services\Payment\B2BPaystackPaymentProcessor;

class PaymentService
{
    use HttpResponse;

    protected \App\Services\Payment\AuthorizeNet\ChargeCardService $chargeCardService;

    public function __construct(ChargeCardService $chargeCardService)
    {
        $this->chargeCardService = $chargeCardService;
    }

    public function processPayment($request)
    {
        $paymentProcessor = match ($request->payment_method) {
            PaymentType::PAYSTACK => new PaystackPaymentProcessor(),
            PaymentType::B2B_PAYSTACK => new B2BPaystackPaymentProcessor(),
            default => throw new \Exception("Unsupported payment method"),
        };

        $paymentService = new HandlePaymentService($paymentProcessor);

        $paymentDetails = match ($request->payment_method) {
            PaymentType::PAYSTACK => PaymentDetailsService::paystackPayDetails($request),
            PaymentType::B2B_PAYSTACK => PaymentDetailsService::b2bPaystackPayDetails($request),
            default => throw new \Exception("Unsupported payment method"),
        };

        return $paymentService->process($paymentDetails);
    }

    public function webhook($request)
    {
        $secretKey = config('paystack.secretKey');
        $signature = $request->header('x-paystack-signature');
        $payload = $request->getContent();

        if (!$signature || $signature !== hash_hmac('sha512', $payload, $secretKey)) {
            return $this->error(null, 'Invalid signature', 400);
        }

        $event = json_decode($payload, true);

        if (!isset($event['event']) || !isset($event['data'])) {
            return $this->error(null, 'Invalid payload', 400);
        }

        if ($event['event'] === PaystackEvent::CHARGE_SUCCESS) {
            $data = $event['data'];
            $paymentType = $data['metadata']['payment_type'];

            switch ($paymentType) {
                case PaymentType::RECURRINGCHARGE:
                    PaystackService::handleRecurringCharge($event, $event['event']);
                    break;

                case PaymentType::USERORDER:
                    PaystackService::handlePaymentSuccess($event, $event['event']);
                    break;

                case PaymentType::B2BUSERORDER:
                    PaystackService::handleB2BPaymentSuccess($event, $event['event']);
                    break;

                default:
                    Log::warning('Unknown payment type', ['payment_type' => $paymentType]);
                    break;
            }
        }

        $eventType = $event['event'];
        $data = $event['data'];

        switch ($eventType) {
            case PaystackEvent::TRANSFER_SUCCESS:
                PaystackService::handleTransferSuccess($data);
                break;

            case PaystackEvent::TRANSFER_FAILED:
                PaystackService::handleTransferFailed($data);
                break;

            default:
                Log::warning("Unhandled Paystack event: {$eventType}", $data);
                break;
        }

        return response()->json(['status' => true], 200);
    }

    public function verifyPayment($userId, $ref)
    {
        $currentUserId = Auth::id();
        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        if (!preg_match('/^[A-Za-z0-9]{10,30}$/', $ref)) {
            return $this->error(null, 400, "Invalid payment reference.");
        }

        $verify = (new GetCurlService($ref))->run();
        $data = new PaymentVerifyResource($verify);

        return $this->success($data, "Payment verify status");
    }

    public function authorizeNetCard($request)
    {
        if ($request->type == "b2b") {
            return $this->chargeCardService->processB2BPayment($request->all());
        }
        return $this->chargeCardService->processPayment($request->all());
    }

    public function getPaymentMethod($countryId)
    {
        $services = ModelPaymentService::whereHas('countries', function ($q) use ($countryId): void {
            $q->where('country_id', $countryId);
        })->with('countries')->get();

        $data = $services->map(function ($service): array {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'slug' => $service->slug,
            ];
        });

        return $this->success($data, "Payment methods");
    }

    public function getBanks()
    {
        $banks = Cache::remember('banks_list', 43200, function () {
            $banks = Bank::select('id', 'name', 'slug', 'code')->get();

            if ($banks->isNotEmpty()) {
                return $banks;
            }

            return null;
        });

        if (empty($banks)) {
            return $this->error('No banks found', 404);
        }

        return $this->success($banks, 'Banks retrieved successfully');
    }

    public function accountLookUp($request)
    {
        $url = config('services.paystack.bank_base_url') . "/resolve?account_number=". $request->account_number . "&bank_code=". $request->bank_code;

        $token = config('services.paystack.test_sk');

        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];

        return (new GetCurl($url, $headers))->execute();
    }
}
