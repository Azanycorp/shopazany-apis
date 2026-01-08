<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddAttributeRequest;
use App\Http\Requests\BusinessInfoRequest;
use App\Http\Requests\ProductImportRequest;
use App\Http\Requests\ProductRequest;
use App\Services\User\SellerService;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function __construct(
        protected SellerService $service
    ) {}

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

    public function getProduct(Request $request, $userId)
    {
        return $this->service->getProduct($request, $userId);
    }

    public function getSingleProduct($productId, $userId)
    {
        return $this->service->getSingleProduct($productId, $userId);
    }

    public function getAllOrders(Request $request, $userId)
    {
        return $this->service->getAllOrders($request, $userId);
    }

    public function getOrderDetail($userId, $id)
    {
        return $this->service->getOrderDetail($userId, $id);
    }

    public function updateOrderStatus($userId, $id, Request $request)
    {
        $request->validate([
            'status' => ['required', 'string'],
        ]);

        return $this->service->updateOrderStatus($userId, $id, $request);
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

    public function getOrderSummary($userId, Request $request)
    {
        return $this->service->getOrderSummary($userId, $request);
    }

    public function topSelling($userId)
    {
        return $this->service->topSelling($userId);
    }

    public function createAttribute(AddAttributeRequest $request)
    {
        return $this->service->createAttribute($request);
    }

    public function getAttribute($userId)
    {
        return $this->service->getAttribute($userId);
    }

    public function getSingleAttribute($id, $userId)
    {
        return $this->service->getSingleAttribute($id, $userId);
    }

    public function updateAttribute(Request $request, $id, $userId)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'values' => ['required', 'array'],
            'use_for_variation' => ['required', 'boolean'],
        ]);

        return $this->service->updateAttribute($request, $id, $userId);
    }

    public function deleteAttribute($id, $userId)
    {
        return $this->service->deleteAttribute($id, $userId);
    }
}
