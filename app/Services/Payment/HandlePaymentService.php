<?php

namespace App\Services\Payment;

use App\Contracts\PaymentStrategy;

class HandlePaymentService
{
    public function __construct(
        protected PaymentStrategy $paymentProcessor
    ) {}

    public function process(array $paymentDetails)
    {
        return $this->paymentProcessor->processPayment($paymentDetails);
    }
}
