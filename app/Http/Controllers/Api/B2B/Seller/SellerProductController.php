<?php

namespace App\Http\Controllers\Api\B2B\Seller;

use Illuminate\Http\Request;
use App\Services\B2B\SellerService;
use App\Http\Controllers\Controller;
use App\Http\Requests\B2B\AddProductRequest;

class SellerProductController extends Controller
{
    public function __construct(
        private SellerService $service
    ) {}


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
}
