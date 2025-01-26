<?php

namespace App\Services\Payment;

use App\Contracts\PaymentStrategy;

class HandlePaymentService
{
    protected \App\Contracts\PaymentStrategy $paymentProcessor;

    public function __construct(PaymentStrategy $paymentProcessor)
    {
        $this->paymentProcessor = $paymentProcessor;
    }

    public function process(array $paymentDetails)
    {
        return $this->paymentProcessor->processPayment($paymentDetails);
    }
}


