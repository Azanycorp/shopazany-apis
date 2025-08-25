<?php

namespace App\Http\Controllers\Api\AgriEcom;

use App\Http\Controllers\Controller;
use App\Services\AgriEcom\SellerService;
use App\Services\B2B\SellerService as B2BSellerService;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function __construct(
        protected SellerService $sellerService,
        protected B2BSellerService $b2bSellerService
    )
    {}
    
    public function createBusinessInformation(Request $request)
    {
        return $this->b2bSellerService->businessInformation($request);
    }
}
