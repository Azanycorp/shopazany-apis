<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\ShippingAgent;
use App\Models\CollationCenter;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Services\Payment\PaymentService;
use App\Http\Resources\ShippingAgentResource;
use App\Http\Requests\AuthorizeNetCardRequest;
use App\Http\Requests\B2BAuthorizeNetCardRequest;

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
        $centres = CollationCenter::with('hubs')->latest('id')->get();
        $data = ShippingAgentResource::collection($agents);
        $details = [
            'centres' => $centres,
            'agents' => $data
        ];
        return $this->success($details, 'Available Agents and Collation centres');
    }

    public function processPayment(PaymentRequest $request)
    {
        return $this->service->processPayment($request);
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
