<?php

namespace App\Http\Controllers\Api\AgriEcom;

use App\Http\Controllers\Controller;
use App\Services\AgriEcom\SellerService;
use App\Services\B2B\SellerService as B2BSellerService;
use App\Services\User\SellerService as B2CSellerService;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function __construct(
        protected SellerService $sellerService,
        protected B2BSellerService $b2bSellerService,
        protected B2CSellerService $b2cSellerService
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
}
