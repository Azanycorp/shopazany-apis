<?php

namespace App\Http\Controllers\Api\B2B;

use Illuminate\Http\Request;
use App\Services\B2B\BuyerService;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\B2B\SignupRequest;
use App\Services\AgriEcom\B2B\AuthService;
use App\Services\B2B\SellerService;
use App\Http\Requests\B2B\BuyerOnboardingRequest;
use App\Http\Requests\B2B\BusinessInformationRequest;

class B2BAuthController extends Controller
{

    public function __construct(
        protected AuthService $service,
        protected SellerService $sellerService,
        protected BuyerService $buyerService
    ) {}

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
