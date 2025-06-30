<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountLookupRequest;
use App\Http\Requests\AuthorizeNetCardRequest;
use App\Http\Requests\B2BAuthorizeNetCardRequest;
use App\Http\Requests\PaymentRequest;
use App\Http\Resources\ShippingAgentResource;
use App\Models\CollationCenter;
use App\Models\ShippingAgent;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $service
    ) {}

    public function getShippingAgents()
    {
        $agents = ShippingAgent::latest()->get();

        $centres = CollationCenter::with('hubs')->latest()->get();

        $data = ShippingAgentResource::collection($agents);

        $details = [
            'centres' => $centres,
            'agents' => $data,
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

    public function accountLookup(AccountLookupRequest $request)
    {
        return $this->service->accountLookup($request);
    }

    public function approveTransfer(Request $request)
    {
        return $this->service->approveTransfer($request);
    }
}
