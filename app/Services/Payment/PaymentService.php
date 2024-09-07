<?php

namespace App\Services\Payment;

use App\Models\PaymentLog;
use App\Models\User;
use App\Trait\HttpResponse;
use Unicodeveloper\Paystack\Facades\Paystack;

class PaymentService
{
    use HttpResponse;

    public function processPayment($request)
    {
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
            'currency' => 'NGN',
            'metadata' => json_encode([
                'shipping_address' => $address,
                'items' => $request->input('items'),
                'payment_method' => $request->input('payment_method'),
            ]),
            'frontend_url' => $request->input('payment_redirect_url')
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

        if ($event['event'] === 'charge.success') {
            $this->handlePaymentSuccess($event, $event['event']);
        }

        return response()->json(['status' => true], 200);
    }

    protected function handlePaymentSuccess($event, $status)
    {
        $paymentData = $event['data'];

        PaymentLog::create([
            'data' => $paymentData,
            'status' => $status,
        ]);
    }
    
}



