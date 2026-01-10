<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddAttributeRequest;
use App\Http\Requests\BusinessInfoRequest;
use App\Http\Requests\ProductImportRequest;
use App\Http\Requests\ProductRequest;
use App\Services\User\SellerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function __construct(
        protected SellerService $service
    ) {}

    public function businessInfo(BusinessInfoRequest $request): JsonResponse
    {
        return $this->service->businessInfo($request);
    }

    public function createProduct(ProductRequest $request): JsonResponse
    {
        return $this->service->createProduct($request);
    }

    public function updateProduct(Request $request, int $id, int $userId): JsonResponse
    {
        return $this->service->updateProduct($request, $id, $userId);
    }

    public function deleteProduct(int $id, int $userId): JsonResponse
    {
        return $this->service->deleteProduct($id, $userId);
    }

    public function getProduct(Request $request, int $userId): JsonResponse
    {
        return $this->service->getProduct($request, $userId);
    }

    public function getSingleProduct(int $productId, int $userId): JsonResponse
    {
        return $this->service->getSingleProduct($productId, $userId);
    }

    public function getAllOrders(Request $request, int $userId): array
    {
        return $this->service->getAllOrders($request, $userId);
    }

    public function getOrderDetail(int $userId, int $id): JsonResponse
    {
        return $this->service->getOrderDetail($userId, $id);
    }

    public function updateOrderStatus(int $userId, int $id, Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string'],
        ]);

        return $this->service->updateOrderStatus($userId, $id, $request);
    }

    public function getTemplate(): JsonResponse
    {
        return $this->service->getTemplate();
    }

    public function productImport(ProductImportRequest $request): JsonResponse
    {
        return $this->service->productImport($request);
    }

    public function export(int $userId, string $type): JsonResponse
    {
        return $this->service->export($userId, $type);
    }

    public function dashboardAnalytics(int $userId): JsonResponse
    {
        return $this->service->dashboardAnalytics($userId);
    }

    public function getOrderSummary(int $userId, Request $request): JsonResponse
    {
        return $this->service->getOrderSummary($userId, $request);
    }

    public function topSelling(int $userId): JsonResponse
    {
        return $this->service->topSelling($userId);
    }

    public function createAttribute(AddAttributeRequest $request): JsonResponse
    {
        return $this->service->createAttribute($request);
    }

    public function getAttribute(int $userId): JsonResponse
    {
        return $this->service->getAttribute($userId);
    }

    public function getSingleAttribute(int $id, int $userId): JsonResponse
    {
        return $this->service->getSingleAttribute($id, $userId);
    }

    public function updateAttribute(Request $request, int $id, int $userId): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string'],
            'values' => ['required', 'array'],
            'use_for_variation' => ['required', 'boolean'],
        ]);

        return $this->service->updateAttribute($request, $id, $userId);
    }

    public function deleteAttribute(int $id, int $userId): JsonResponse
    {
        return $this->service->deleteAttribute($id, $userId);
    }
}
