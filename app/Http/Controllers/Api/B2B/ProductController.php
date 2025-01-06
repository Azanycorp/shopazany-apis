<?php

namespace App\Http\Controllers\Api\B2B;

use Illuminate\Http\Request;
use App\Services\B2B\BuyerService;
use App\Services\B2B\SellerService;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{

    public function __construct(
        private SellerService $sellerService,
        private BuyerService $buyerService
    ) {}
    
    public function getProducts()
    {
        return $this->buyerService->getProducts();
    }

    public function getProductDetail($slug)
    {
        return $this->buyerService->getProductDetail($slug);
    }
}
