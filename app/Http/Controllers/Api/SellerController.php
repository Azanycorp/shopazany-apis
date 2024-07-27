<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessInfoRequest;
use App\Http\Requests\ProductRequest;
use App\Services\User\SellerService;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    protected $service;

    public function __construct(SellerService $sellerService)
    {
        $this->service = $sellerService;
    }

    public function businessInfo(BusinessInfoRequest $request)
    {
        return $this->service->businessInfo($request);
    }

    public function createProduct(ProductRequest $request)
    {
        return $this->service->createProduct($request);
    }

    public function updateProduct(ProductRequest $request, $id, $userId)
    {
        return $this->service->updateProduct($request, $id, $userId);
    }

    public function deleteProduct($id)
    {
        return $this->service->deleteProduct($id);
    }

    public function getProduct($userId)
    {
        return $this->service->getProduct($userId);
    }

    public function getSingleProduct($productId, $userId)
    {
        return $this->service->getSingleProduct($productId, $userId);
    }

    public function getAllOrders($userId)
    {
        return $this->service->getAllOrders($userId);
    }

    public function getConfirmedOrders($userId)
    {
        return $this->service->getConfirmedOrders($userId);
    }

    public function getCancelledOrders($userId)
    {
        return $this->service->getCancelledOrders($userId);
    }

    public function getDeliveredOrders($userId)
    {
        return $this->service->getDeliveredOrders($userId);
    }

    public function getPendingOrders($userId)
    {
        return $this->service->getPendingOrders($userId);
    }

    public function getProcessingOrders($userId)
    {
        return $this->service->getProcessingOrders($userId);
    }

    public function getShippedOrders($userId)
    {
        return $this->service->getShippedOrders($userId);
    }
}


