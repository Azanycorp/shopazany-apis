<?php

namespace App\Http\Controllers\Api\AgriEcom;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgriEcom\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AgriEcom\AuthService;
use App\Services\Auth\AuthService as Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected Auth $auth
    )
    {}

    public function login(LoginRequest $request)
    {
        return $this->auth->login($request);
    }

    public function register(RegisterRequest $request)
    {
        return $this->authService->register($request);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string',
        ]);

        return $this->authService->verify($request);
    }

    public function resendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        return $this->authService->resendCode($request);
    }
}
