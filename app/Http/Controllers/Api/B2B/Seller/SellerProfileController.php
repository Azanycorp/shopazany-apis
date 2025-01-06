<?php

namespace App\Http\Controllers\Api\B2B\Seller;

use Illuminate\Http\Request;
use App\Services\B2B\SellerService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;

class SellerProfileController extends Controller
{
    public function __construct(
        private SellerService $service
    ) {}

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

}
