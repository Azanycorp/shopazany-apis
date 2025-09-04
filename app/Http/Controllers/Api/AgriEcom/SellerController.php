<?php

namespace App\Http\Controllers\Api\AgriEcom;

use Illuminate\Http\Request;
use App\Services\User\UserService;
use App\Http\Controllers\Controller;
use App\Http\Requests\WithdrawalRequest;
use App\Services\AgriEcom\SellerService;
use App\Http\Requests\AddAttributeRequest;
use App\Http\Requests\ProductImportRequest;
use App\Services\B2B\SellerService as B2BSellerService;
use App\Services\User\SellerService as B2CSellerService;

class SellerController extends Controller
{
    public function __construct(
        protected SellerService $sellerService,
        protected B2BSellerService $b2bSellerService,
        protected B2CSellerService $b2cSellerService,
        protected UserService $userService
    )
    {}

    public function createBusinessInformation(Request $request)
    {
        return $this->b2bSellerService->businessInformation($request);
    }

    public function createProduct(Request $request)
    {
        return $this->b2cSellerService->createProduct($request, "agriecom");
    }

    public function updateProduct(Request $request, $id, $userId)
    {
        return $this->b2cSellerService->updateProduct($request, $id, $userId);
    }

    public function deleteProduct($id, $userId)
    {
        return $this->b2cSellerService->deleteProduct($id, $userId);
    }

    public function getProduct($userId)
    {
        return $this->b2cSellerService->getProduct($userId);
    }

    public function getSingleProduct($productId, $userId)
    {
        return $this->b2cSellerService->getSingleProduct($productId, $userId);
    }

    public function topSelling($userId)
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

    public function export($userId, $type)
    {
        return $this->b2cSellerService->export($userId, $type);
    }

    public function createAttribute(AddAttributeRequest $request)
    {
        return $this->b2cSellerService->createAttribute($request);
    }

    public function getAttribute($userId)
    {
        return $this->b2cSellerService->getAttribute($userId);
    }

    public function getSingleAttribute($id, $userId)
    {
        return $this->b2cSellerService->getSingleAttribute($id, $userId);
    }

    public function updateAttribute(Request $request, $id, $userId)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'values' => ['required', 'array'],
            'use_for_variation' => ['required', 'boolean'],
        ]);

        return $this->b2cSellerService->updateAttribute($request, $id, $userId);
    }

    public function deleteAttribute($id, $userId)
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

    public function withdrawalMethod($userId)
    {
        return $this->userService->getPaymentMethod($userId);
    }

    public function withdrawalRequest(WithdrawalRequest $request)
    {
        return $this->userService->withdraw($request);
    }

    public function withdrawalHistory($userId)
    {
        return $this->userService->withdrawalHistory($userId);
    }

    public function profile($userId)
    {
        return $this->sellerService->profile($userId);
    }
}
