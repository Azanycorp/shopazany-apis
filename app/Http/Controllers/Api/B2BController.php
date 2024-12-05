<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\B2B\BusinessInformationRequest;
use App\Http\Requests\B2B\SignupRequest;
use App\Http\Requests\LoginRequest;
use App\Services\B2B\Auth\AuthService;
use App\Services\B2B\SellerService;
use Illuminate\Http\Request;
use App\Http\Requests\B2B\BuyerOnboardingRequest;

class B2BController extends Controller
{
    protected $service;
    protected $sellerService;

    public function __construct(AuthService $service, SellerService $sellerService)
    {
        $this->service = $service;
        $this->sellerService = $sellerService;
    }

    public function login(LoginRequest $request)
    {
        return $this->service->login($request);
    }

    public function signup(SignupRequest $request)
    {
        return $this->service->signup($request);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string',
        ]);

        return $this->service->verify($request);
    }

    public function resendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        return $this->service->resendCode($request);
    }

    public function businessInformation(BusinessInformationRequest $request)
    {
        return $this->sellerService->businessInformation($request);
    }
    
    public function buyerOnboarding(BuyerOnboardingRequest $request)
    {
        return $this->service->buyerOnboarding($request);
    }
}
