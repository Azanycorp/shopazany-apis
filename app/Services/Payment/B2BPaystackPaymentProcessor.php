<?php

namespace App\Services\Payment;

use App\Contracts\PaymentStrategy;
use App\Trait\HttpResponse;
use Unicodeveloper\Paystack\Facades\Paystack;

class B2BPaystackPaymentProcessor implements PaymentStrategy
{
    use HttpResponse;

    public function processPayment(array $paymentDetails): array
    {
        try {
            $paystackInstance = Paystack::getAuthorizationUrl($paymentDetails);
            return [
                'status' => 'success',
                'data' => $paystackInstance,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ];
        }
    }
}



