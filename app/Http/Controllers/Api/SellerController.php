<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessInfoRequest;
use App\Http\Requests\ProductImportRequest;
use App\Http\Requests\ProductRequest;
use App\Services\User\SellerService;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    protected \App\Services\User\SellerService $service;

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

    public function updateProduct(Request $request, $id, $userId)
    {
        return $this->service->updateProduct($request, $id, $userId);
    }

    public function deleteProduct($id, $userId)
    {
        return $this->service->deleteProduct($id, $userId);
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

    public function getTemplate()
    {
        return $this->service->getTemplate();
    }

    public function productImport(ProductImportRequest $request)
    {
        return $this->service->productImport($request);
    }

    public function export($userId, $type)
    {
        return $this->service->export($userId, $type);
    }

    public function dashboardAnalytics($userId)
    {
        return $this->service->dashboardAnalytics($userId);
    }

    public function getOrderSummary($userId)
    {
        return $this->service->getOrderSummary($userId);
    }

    public function topSelling($userId)
    {
        return $this->service->topSelling($userId);
    }
}


