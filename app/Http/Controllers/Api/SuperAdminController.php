<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Admin\HubRequest;
use App\Http\Requests\AdminUserRequest;
use App\Services\Admin\SuperAdminService;

class SuperAdminController extends Controller
{
    const MESSAGE = '403 Forbidden';

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

}
