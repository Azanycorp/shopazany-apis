<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddUserRequest;
use App\Services\Admin\SuperAdminService;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    const MESSAGE = '403 Forbidden';

    public function __construct(
        protected SuperAdminService $superAdminService,
    ) {}

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

    public function deleteAdmin($userId)
    {
        return $this->superAdminService->deleteAdmin($userId);
    }

    public function addUser(AddUserRequest $request)
    {
        return $this->superAdminService->addUser($request);
    }

    public function security(Request $request)
    {
        return $this->superAdminService->security($request);
    }

    public function verifyCode(Request $request)
    {
        $request->validate(['code' => 'required|string']);

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
