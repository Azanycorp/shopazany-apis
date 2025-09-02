<?php

namespace App\Http\Controllers\Api\AgriEcom;

use Illuminate\Http\Request;
use App\Services\B2B\SellerService;
use App\Http\Controllers\Controller;
use App\Http\Requests\B2B\AddProductRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\B2B\SellerShippingRequest;
use App\Http\Requests\B2B\WithdrawalMethodRequest;

class B2BSellerController extends Controller
{

    public function __construct(
        protected SellerService $service
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

    public function dashboard()
    {
        return $this->service->getDashboardDetails();
    }

    public function getEarningReport()
    {
        return $this->service->getEarningReport();
    }

    public function withdrawalHistory()
    {
        return $this->service->getWithdrawalHistory();
    }

    public function makeWithdrawalRequest(Request $request)
    {
        return $this->service->withdrawalRequest($request);
    }
    public function addShipping(SellerShippingRequest $request)
    {
        return $this->service->addShipping($request);
    }

    public function getAllShipping($user_id)
    {
        return $this->service->getAllShipping($user_id);
    }

    public function getShippingById($user_id, $shipping_id)
    {
        return $this->service->getShippingById($user_id, $shipping_id);
    }

    public function updateShipping(SellerShippingRequest $request, $shipping_id)
    {
        return $this->service->updateShipping($request, $shipping_id);
    }

    public function deleteShipping($user_id, $shipping_id)
    {
        return $this->service->deleteShipping($user_id, $shipping_id);
    }

    public function setDefault($user_id, $shipping_id)
    {
        return $this->service->setDefault($user_id, $shipping_id);
    }

    // Seller Wihdrawal method
    public function allWithdrawalMethods()
    {
        return $this->service->getAllMethod();
    }

    public function addWithdrawalMethod(WithdrawalMethodRequest $request)
    {
        return $this->service->addNewMethod($request);
    }

    public function getWithdrawalMethod($id)
    {
        return $this->service->getSingleMethod($id);
    }

    public function makeDefaultAccount(Request $request)
    {
        return $this->service->makeAccounDefaultt($request);
    }

    public function updateWithdrawalMethod(WithdrawalMethodRequest $request, $id)
    {
        return $this->service->updateMethod($request, $id);
    }

    public function deleteWithdrawalMethod($id)
    {
        return $this->service->deleteMethod($id);
    }

    public function addProduct(AddProductRequest $request)
    {
        return $this->service->addAgricomProduct($request);
    }

}
