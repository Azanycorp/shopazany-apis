<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AffiliateSignupRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\SellerSignUpRequest;
use App\Http\Requests\SignUpRequest;
use App\Http\Requests\VerifyRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function login(LoginRequest $request)
    {
        return $this->authService->login($request);
    }

    public function loginVerify(VerifyRequest $request)
    {
        return $this->authService->loginVerify($request);
    }

    public function signup(SignUpRequest $request)
    {
        return $this->authService->signup($request);
    }

    public function verify(VerifyRequest $request)
    {
        return $this->authService->verify($request);
    }

    public function resendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        return $this->authService->resendCode($request);
    }

    public function forgot(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'email:rfc:dns'],
        ]);

        return $this->authService->forgot($request);
    }

    public function reset(ResetPasswordRequest $request)
    {
        return $this->authService->reset($request);
    }

    public function logout()
    {
        return $this->authService->logout();
    }

    public function affiliateSignup(AffiliateSignupRequest $request)
    {
        return $this->authService->affiliateSignup($request);
    }

    public function sellerSignup(SellerSignUpRequest $request)
    {
        return $this->authService->sellerSignup($request);
    }
}
