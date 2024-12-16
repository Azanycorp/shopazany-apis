<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Services\Payment\PaymentService;
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

    public function authorizeNetCard(Request $request)
    {
        $request->validate([
            'card_number' => 'required',
            'expiration_date' => 'required',
            'cvv' => 'required',
            'amount' => 'required'
        ]);

        return $this->service->authorizeNetCard($request);
    }

}
