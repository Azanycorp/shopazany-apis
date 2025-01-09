<?php

namespace App\Services\Payment;

use App\Contracts\PaymentStrategy;
use App\Trait\HttpResponse;
use Unicodeveloper\Paystack\Facades\Paystack;

class PaystackPaymentProcessor implements PaymentStrategy
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
            return $this->error(null, 'Payment processing failed, please try again later');
        }
    }
}



