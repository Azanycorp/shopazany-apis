<?php

namespace App\Http\Controllers\Api\AgriEcom;

use App\Http\Controllers\Controller;
use App\Http\Requests\B2B\AddProductRequest;
use App\Http\Requests\B2B\SellerShippingRequest;
use App\Http\Requests\B2B\WithdrawalMethodRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Services\B2B\SellerService;
use Illuminate\Http\Request;

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

    public function getAllShipping(int $userId)
    {
        return $this->service->getAllShipping($userId);
    }

    public function getShippingById(int $userId, int $shippingId)
    {
        return $this->service->getShippingById($userId, $shippingId);
    }

    public function updateShipping(SellerShippingRequest $request, int $shippingId)
    {
        return $this->service->updateShipping($request, $shippingId);
    }

    public function deleteShipping(int $userId, int $shippingId)
    {
        return $this->service->deleteShipping($userId, $shippingId);
    }

    public function setDefault(int $userId, int $shippingId)
    {
        return $this->service->setDefault($userId, $shippingId);
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

    public function getWithdrawalMethod(int $id)
    {
        return $this->service->getSingleMethod($id);
    }

    public function makeDefaultAccount(Request $request)
    {
        return $this->service->makeAccounDefaultt($request);
    }

    public function updateWithdrawalMethod(WithdrawalMethodRequest $request, int $id)
    {
        return $this->service->updateMethod($request, $id);
    }

    public function deleteWithdrawalMethod(int $id)
    {
        return $this->service->deleteMethod($id);
    }

    public function productImport(Request $request)
    {
        return $this->service->b2bproductImport($request);
    }

    public function export(Request $request, int $userId)
    {
        return $this->service->exportSellerProduct($request, $userId);
    }

    public function addProduct(AddProductRequest $request)
    {
        return $this->service->addProduct($request);
    }

    public function getAllProduct(Request $request)
    {
        return $this->service->getAllProduct($request);
    }

    public function updateProduct(Request $request)
    {
        return $this->service->updateProduct($request);
    }

    public function getProductById(int $productId, int $userId)
    {
        return $this->service->getProductById($productId, $userId);
    }

    public function deleteProduct(int $userId, int $productId)
    {
        return $this->service->deleteProduct($userId, $productId);
    }

    public function getAnalytics(int $userId)
    {
        return $this->service->getAnalytics($userId);
    }
}
