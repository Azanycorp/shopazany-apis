<?php

namespace App\Contracts;

interface PaymentStrategy
{
    public function processPayment(array $paymentDetails);
}
