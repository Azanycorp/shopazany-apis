<?php

namespace App\Http\Controllers\Api;

use App\Enum\PaymentType;
use Illuminate\Http\Request;
use App\Models\ShippingAgent;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Services\Payment\PaymentService;
use App\Http\Resources\ShippingAgentResource;
use App\Http\Requests\AuthorizeNetCardRequest;
use App\Services\Payment\HandlePaymentService;
use App\Services\Payment\PaymentDetailsService;
use App\Http\Requests\B2BAuthorizeNetCardRequest;
use App\Services\Payment\PaystackPaymentProcessor;
use App\Services\Payment\B2BPaystackPaymentProcessor;

class PaymentController extends Controller
{
    protected \App\Services\Payment\PaymentService $service;

    public function __construct(PaymentService $service)
    {
        $this->service = $service;
    }

    public function getShippingAgents()
    {
        $agents = ShippingAgent::latest('id')->get();
        $data = ShippingAgentResource::collection($agents);
        return $this->success($data, 'Available Agents');
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

    public function b2bAuthorizeNetCard(B2BAuthorizeNetCardRequest $request)
    {
        return $this->service->authorizeNetCard($request);
    }

    public function getPaymentMethod($countryId)
    {
        return $this->service->getPaymentMethod($countryId);
    }

    public function getBanks()
    {
        return $this->service->getBanks();
    }

    public function accountLookup(Request $request)
    {
        $request->validate([
            'account_number' => ['required', 'string'],
            'bank_code' => ['required', 'string'],
        ]);
        return $this->service->accountLookup($request);
    }
}
