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

    public function getProfile($userId)
    {
        return $this->superAdminService->getProfile($userId);
    }
}
