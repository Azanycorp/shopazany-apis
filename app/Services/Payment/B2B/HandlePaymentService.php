<?php

namespace App\Services\Payment\B2B;

use App\Contracts\PaymentStrategy;

class HandlePaymentService
{
    protected $paymentProcessor;

    public function __construct(PaymentStrategy $paymentProcessor)
    {
        $this->paymentProcessor = $paymentProcessor;
    }

    public function process(array $paymentDetails)
    {
        return $this->paymentProcessor->processPayment($paymentDetails);
    }
}


