<?php

namespace App\Services\Payment;

use App\Enum\PaymentType;
use App\Enum\PaystackEvent;
use App\Models\Payment;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\Auth;
use App\Services\Curl\GetCurlService;
use App\Http\Resources\PaymentVerifyResource;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Unicodeveloper\Paystack\Facades\Paystack;

class PaymentService
{
    use HttpResponse;

    public function processPayment($request)
    {

        if($request->input('currency') === 'USD') {
            return $this->error(null, 'Currrency not available at the moment');
        }

        $user = User::findOrFail($request->user_id);

        $amount = $request->input('amount') * 100;
        $user_shipping = $request->input('user_shipping_address_id');

        if (!$user_shipping) {
            $address = (object) [
                'first_name' => $request->shipping_address['first_name'],
                'last_name' => $request->shipping_address['last_name'],
                'email' => $request->shipping_address['email'],
                'phone' => $request->shipping_address['phone'],
                'street_address' => $request->shipping_address['street_address'],
                'state' => $request->shipping_address['state'],
                'city' => $request->shipping_address['city'],
                'zip' => $request->shipping_address['zip'],
            ];
        } else {
            $addr = $user->userShippingAddress()->where('id', $user_shipping)->first();
            $address = $addr;
        }

        $callbackUrl = $request->input('payment_redirect_url');
        if (!filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
            return response()->json(['error' => 'Invalid callback URL'], 400);
        }

        $paymentDetails = [
            'email' => $request->input('email'),
            'amount' => $amount,
            'currency' => $request->input('currency'),
            'metadata' => json_encode([
                'user_id' => $request->input('user_id'),
                'shipping_address' => $address,
                'items' => $request->input('items'),
                'payment_method' => $request->input('payment_method'),
                'payment_type' => PaymentType::USERORDER,
            ]),
            'callback_url' => $request->input('payment_redirect_url')
        ];

        $paystackInstance = Paystack::getAuthorizationUrl($paymentDetails);
        return response()->json($paystackInstance);
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

}



