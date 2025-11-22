<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\SuperAdminAuthService;
use Illuminate\Http\Request;

class SuperAdminAuthController extends Controller
{
    public function __construct(
        protected SuperAdminAuthService $superAdminAuthService,
    ) {}

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:admins,email'],
            'password' => ['required', 'string'],
        ]);

        return $this->superAdminAuthService->login($request);
    }

    public function forgot(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:admins,email'],
        ]);

        return $this->superAdminAuthService->forgot($request);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string'],
        ]);

        return $this->superAdminAuthService->verifyEmail($request);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        return $this->superAdminAuthService->reset($request);
    }
}
