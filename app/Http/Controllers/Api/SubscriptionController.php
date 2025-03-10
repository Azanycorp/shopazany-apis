<?php

namespace App\Http\Controllers\Api;

use App\Enum\PaymentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubscriptionPaymentRequest;
use App\Services\Payment\AuthorizeNetSubscriptionPaymentProcessor;
use App\Services\Payment\HandlePaymentService;
use App\Services\Payment\PaymentDetailsService;
use App\Services\Payment\PaystackPaymentProcessor;
use App\Services\SubscriptionService;
use App\Trait\HttpResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    use HttpResponse;

    protected \App\Services\SubscriptionService $service;

    public function __construct(SubscriptionService $service)
    {
        $this->service = $service;
    }

    public function getPlanByCountry($countryId)
    {
        return $this->service->getPlanByCountry($countryId);
    }

    public function subscriptionPayment(SubscriptionPaymentRequest $request)
    {
        $paymentProcessor = match ($request->type) {
            PaymentType::PAYSTACK => new PaystackPaymentProcessor(),
            PaymentType::AUTHORIZE => new AuthorizeNetSubscriptionPaymentProcessor(),
            default => throw new \Exception("Unsupported payment method"),
        };

        $paymentService = new HandlePaymentService($paymentProcessor);

        $paymentDetails = match($request->type) {
            PaymentType::PAYSTACK => PaymentDetailsService::paystackSubcriptionPayDetails($request),
            PaymentType::AUTHORIZE => PaymentDetailsService::authorizeNetSubcriptionPayDetails($request),
            default => throw new \Exception("Unsupported payment method"),
        };

        if (isset($paymentDetails['error'])) {
            return $this->error(null, $paymentDetails['error'], 400);
        }

        return $paymentService->process($paymentDetails);
    }

    public function subscriptionHistory($userId)
    {
        return $this->service->subscriptionHistory($userId);
    }

}
