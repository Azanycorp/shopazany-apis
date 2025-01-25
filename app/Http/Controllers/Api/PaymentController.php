<?php

namespace App\Http\Controllers\Api;

use App\Enum\PaymentType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Services\Payment\PaymentService;
use App\Http\Requests\AuthorizeNetCardRequest;
use App\Services\Payment\HandlePaymentService;
use App\Services\Payment\PaymentDetailsService;
use App\Services\Payment\PaystackPaymentProcessor;
use App\Services\Payment\B2BPaystackPaymentProcessor;

class PaymentController extends Controller
{
    protected $service;

    public function __construct(PaymentService $service)
    {
        $this->service = $service;
    }

    public function processPayment(PaymentRequest $request)
    {
        $paymentProcessor = match ($request->payment_method) {
            PaymentType::PAYSTACK => new PaystackPaymentProcessor(),
            PaymentType::B2B_PAYSTACK => new B2BPaystackPaymentProcessor(),
            default => throw new \Exception("Unsupported payment method"),
        };

        $paymentService = new HandlePaymentService($paymentProcessor);

        $paymentDetails = match ($request->payment_method) {
            PaymentType::PAYSTACK => PaymentDetailsService::paystackPayDetails($request),
            PaymentType::B2B_PAYSTACK => PaymentDetailsService::b2bPaystackPayDetails($request),
            default => throw new \Exception("Unsupported payment method"),
        };

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
