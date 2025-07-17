<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\SuperAdminService;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function __construct(
        protected SuperAdminService $superAdminService,
    )
    {}

    public function clearCache()
    {
        return $this->superAdminService->clearCache();
    }

    public function runMigration()
    {
        return $this->superAdminService->runMigration();
    }

    public function seedRun()
    {
       return $this->superAdminService->seedRun();
    }

    public function getProfiles()
    {
       return $this->superAdminService->getProfiles();
    }

    public function getProfile($userId)
    {
        return $this->superAdminService->getProfile($userId);
    }

    public function addUser(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email'],
            'phone_number' => ['required', 'string'],
            'modules' => ['required', 'array']
        ]);

        return $this->superAdminService->addUser($request);
    }

    public function security(Request $request)
    {
        return $this->superAdminService->security($request);
    }

    public function verifyCode(Request $request)
    {
        $request->validate(['code'  => 'required|string']);

        return $this->superAdminService->verifyCode($request);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:admins,id'],
            'password' => ['required', 'string', 'min:8'],
            'confirm_password' => ['required', 'string', 'same:password'],
        ]);

        return $this->superAdminService->changePassword($request);
    }
}
