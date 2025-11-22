<?php

namespace App\Http\Controllers\Api\AgriEcom;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddAttributeRequest;
use App\Http\Requests\B2B\BusinessInformationRequest;
use App\Http\Requests\ProductImportRequest;
use App\Http\Requests\WithdrawalRequest;
use App\Services\AgriEcom\SellerService;
use App\Services\B2B\SellerService as B2BSellerService;
use App\Services\User\SellerService as B2CSellerService;
use App\Services\User\UserService;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function __construct(
        protected SellerService $sellerService,
        protected B2BSellerService $b2bSellerService,
        protected B2CSellerService $b2cSellerService,
        protected UserService $userService
    ) {}

    public function createBusinessInformation(BusinessInformationRequest $request)
    {
        return $this->b2bSellerService->businessInformation($request);
    }

    public function createProduct(Request $request)
    {
        return $this->b2cSellerService->createProduct($request, 'agriecom');
    }

    public function updateProduct(Request $request, int $id, int $userId)
    {
        return $this->b2cSellerService->updateProduct($request, $id, $userId);
    }

    public function deleteProduct(int $id, int $userId)
    {
        return $this->b2cSellerService->deleteProduct($id, $userId);
    }

    public function getProduct(Request $request, int $userId)
    {
        return $this->b2cSellerService->getProduct($request, $userId);
    }

    public function getSingleProduct(int $productId, int $userId)
    {
        return $this->b2cSellerService->getSingleProduct($productId, $userId);
    }

    public function topSelling(int $userId)
    {
        return $this->b2cSellerService->topSelling($userId);
    }

    public function getTemplate()
    {
        return $this->b2cSellerService->getTemplate();
    }

    public function productImport(ProductImportRequest $request)
    {
        return $this->b2cSellerService->productImport($request);
    }

    public function export(int $userId, string $type)
    {
        return $this->b2cSellerService->export($userId, $type);
    }

    public function createAttribute(AddAttributeRequest $request)
    {
        return $this->b2cSellerService->createAttribute($request);
    }

    public function getAttribute(int $userId)
    {
        return $this->b2cSellerService->getAttribute($userId);
    }

    public function getSingleAttribute(int $id, int $userId)
    {
        return $this->b2cSellerService->getSingleAttribute($id, $userId);
    }

    public function updateAttribute(Request $request, int $id, int $userId)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'values' => ['required', 'array'],
            'use_for_variation' => ['required', 'boolean'],
        ]);

        return $this->b2cSellerService->updateAttribute($request, $id, $userId);
    }

    public function deleteAttribute(int $id, int $userId)
    {
        return $this->b2cSellerService->deleteAttribute($id, $userId);
    }

    public function addMethod(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'type' => ['required', 'in:bank_transfer'],
            'platform' => ['required', 'in:paystack,authorize'],
            'is_default' => ['required', 'boolean'],
        ]);

        return $this->userService->addPaymentMethod($request);
    }

    public function withdrawalMethod(int $userId)
    {
        return $this->userService->getPaymentMethod($userId);
    }

    public function withdrawalRequest(WithdrawalRequest $request)
    {
        return $this->userService->withdraw($request);
    }

    public function withdrawalHistory(Request $request, int $userId)
    {
        return $this->userService->withdrawalHistory($request, $userId);
    }

    public function profile(int $userId)
    {
        return $this->sellerService->profile($userId);
    }

    public function editProfile(Request $request)
    {
        $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'middlename' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'date_of_birth' => ['nullable', 'date'],
            'bio' => ['nullable', 'string'],
            'gender' => ['nullable', 'string'],
            'image' => ['nullable', 'image'],
        ]);

        return $this->sellerService->editProfile($request);
    }

    public function getAllOrders(Request $request, int $userId)
    {
        return $this->b2cSellerService->getAllOrders($request, $userId);
    }

    public function getOrderDetail(int $userId, int $id)
    {
        return $this->b2cSellerService->getOrderDetail($userId, $id);
    }

    public function updateOrderStatus(int $userId, int $id, Request $request)
    {
        $request->validate([
            'status' => ['required', 'string'],
        ]);

        return $this->b2cSellerService->updateOrderStatus($userId, $id, $request);
    }

    public function getOrderSummary(int $userId)
    {
        return $this->b2cSellerService->getOrderSummary($userId);
    }
}
