<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Services\B2B\SellerService;
use Illuminate\Http\Request;

class B2BSellerController extends Controller
{
    protected $service;

    public function __construct(SellerService $service)
    {
        $this->service = $service;
    }

    public function profile()
    {
        return $this->service->profile();
    }

    public function editAccount(Request $request)
    {
        return $this->service->editAccount($request);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        return $this->service->changePassword($request);
    }

    public function editCompany(Request $request)
    {
        return $this->service->editCompany($request);
    }

    public function addProduct(Request $request)
    {
        return $this->service->addProduct($request);
    }
}
