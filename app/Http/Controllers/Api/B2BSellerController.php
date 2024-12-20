<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\B2B\AddProductRequest;
use App\Http\Requests\B2B\SellerShippingRequest;
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

    public function addProduct(AddProductRequest $request)
    {
        return $this->service->addProduct($request);
    }

    public function getAllProduct(Request $request)
    {
        return $this->service->getAllProduct($request);
    }

    public function getProductById($user_id, $product_id)
    {
        return $this->service->getProductById($user_id, $product_id);
    }

    public function updateProduct(Request $request)
    {
        return $this->service->updateProduct($request);
    }

    public function deleteProduct($user_id, $product_id)
    {
        return $this->service->deleteProduct($user_id, $product_id);
    }

    public function getAnalytics($user_id)
    {
        return $this->service->getAnalytics($user_id);
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

    public function updateShipping(Request $request, $shipping_id)
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

    public function getComplaints($user_id)
    {
        return $this->service->getComplaints($user_id);
    }

    public function getTemplate()
    {
        return $this->service->getTemplate();
    }

    public function getEarningReport($userId)
    {
        return $this->service->getEarningReport($userId);
    }

}
