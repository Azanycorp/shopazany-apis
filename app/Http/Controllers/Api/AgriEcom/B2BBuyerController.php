<?php

namespace App\Http\Controllers\Api\AgriEcom;

use App\Http\Controllers\Controller;
use App\Http\Requests\B2BBuyerShippingAddressRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Services\B2B\BuyerService;
use Illuminate\Http\Request;

class B2BBuyerController extends Controller
{

    public function __construct(
        protected BuyerService $buyerService
    ) {}

    // Account section
    public function profile()
    {
        return $this->buyerService->profile();
    }

    public function editAccount(Request $request)
    {
        return $this->buyerService->editAccount($request);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        return $this->buyerService->changePassword($request);
    }

    public function change2Fa(Request $request)
    {
        return $this->buyerService->change2FA($request);
    }

    public function editCompany(Request $request)
    {
        return $this->buyerService->editCompany($request);
    }
    // Shipping Address

    public function addShippingAddress(B2BBuyerShippingAddressRequest $request)
    {
        return $this->buyerService->addShippingAddress($request);
    }

    public function allShippingAddress()
    {
        return $this->buyerService->getAllShippingAddress();
    }
    
}
