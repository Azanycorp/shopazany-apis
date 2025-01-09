<?php

namespace App\Http\Controllers\Api;

use App\Enum\PaymentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthorizeNetCardRequest;
use App\Http\Requests\PaymentRequest;
use App\Services\Payment\HandlePaymentService;
use App\Services\Payment\PaymentDetailsService;
use App\Services\Payment\PaymentService;
use App\Services\Payment\PaystackPaymentProcessor;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $service;

    public function __construct(PaymentService $service)
    {
        $this->service = $service;
    }

    public function processPayment(PaymentRequest $request)
    {
        match($request->payment_method) {
            PaymentType::PAYSTACK => $paymentProcessor = new PaystackPaymentProcessor(),
        };

        $paymentService = new HandlePaymentService($paymentProcessor);

        $paymentDetails = PaymentDetailsService::paystackPayDetails($request);

        return $paymentService->process($paymentDetails);
    }

    public function webhook(Request $request)
    {
        return $this->service->webhook($request);
    }

    public function verifyPayment($userId, $ref)
    {
        return $this->service->verifyPayment($userId, $ref);
    }

    public function authorizeNetCard(AuthorizeNetCardRequest $request)
    {
        return $this->service->authorizeNetCard($request);
    }

    public function getPaymentMethod($countryId)
    {
        return $this->service->getPaymentMethod($countryId);
    }

}
