<?php

namespace App\Services\Payment;

use App\Models\User;
use App\Models\Payment;
use App\Enum\PaymentType;
use App\Enum\PaystackEvent;
use App\Trait\HttpResponse;
use App\Contracts\PaymentStrategy;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\Curl\GetCurlService;
use App\Http\Resources\PaymentVerifyResource;
use Unicodeveloper\Paystack\Facades\Paystack;
use App\Models\PaymentService as ModelPaymentService;
use App\Services\Payment\AuthorizeNet\ChargeCardService;

class PaymentService implements PaymentStrategy
{
    use HttpResponse;

    protected $chargeCardService;

    public function __construct(ChargeCardService $chargeCardService)
    {
        $this->chargeCardService = $chargeCardService;
    }

    public function processPayment(array $paymentDetails): array
    {
        try {
            $paystackInstance = Paystack::getAuthorizationUrl($paymentDetails);
            return [
                'status' => 'success',
                'data' => $paystackInstance,
            ];
        } catch (\Exception $e) {
            return $this->error(null, 'Payment processing failed, please try again later');
        }
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

        if (isset($event['event']) && $event['event'] === PaystackEvent::CHARGE_SUCCESS) {
            $data = $event['data'];
            $paymentType = $data['metadata']['payment_type'];

            switch ($paymentType) {
                case PaymentType::RECURRINGCHARGE:
                    PaystackService::handleRecurringCharge($event, $event['event']);
                    break;

                case PaymentType::USERORDER:
                    PaystackService::handlePaymentSuccess($event, $event['event']);
                    break;

                default:
                    Log::warning('Unknown payment type', ['payment_type' => $paymentType]);
                    break;
            }

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
        return $this->chargeCardService->processPayment($request->all());
    }

    public function getPaymentMethod($countryId)
    {
        $services = ModelPaymentService::whereHas('countries', function ($q) use ($countryId) {
            $q->where('country_id', $countryId);
        })->with('countries')->get();

        $data = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'slug' => $service->slug,
            ];
        });

        return $this->success($data, "Payment methods");
    }


}



