<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\AffiliateService;
use Illuminate\Http\Request;

class AdminAffiliateController extends Controller
{
    public function __construct(protected AffiliateService $affiliateService) {}

    public function overview()
    {
        return $this->affiliateService->overview();
    }

    public function allUsers()
    {
        return $this->affiliateService->allUsers();
    }

    public function userDetail($id)
    {
        return $this->affiliateService->userDetail($id);
    }

    public function suspend($id)
    {
        return $this->affiliateService->suspend($id);
    }

    public function resetPassword(Request $request)
    {
        return $this->affiliateService->resetPassword($request);
    }
}
