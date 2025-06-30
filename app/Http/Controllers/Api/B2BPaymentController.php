<?php

namespace App\Http\Controllers\Api;

use App\Enum\PaymentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthorizeNetCardRequest;
use App\Http\Requests\B2BPaymentRequest;
use App\Services\Payment\HandlePaymentService;
use App\Services\Payment\PaymentDetailsService;
use App\Services\Payment\PaymentService;
use App\Services\Payment\PaystackPaymentProcessor;
use Illuminate\Http\Request;

class B2BPaymentController extends Controller
{
    public function __construct(
        protected PaymentService $service
    ) {}

    public function processPayment(B2BPaymentRequest $request)
    {
        match ($request->payment_method) {
            PaymentType::PAYSTACK => $paymentProcessor = new PaystackPaymentProcessor
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
