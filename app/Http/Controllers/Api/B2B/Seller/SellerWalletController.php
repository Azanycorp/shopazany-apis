<?php

namespace App\Http\Controllers\Api\B2B\Seller;

use Illuminate\Http\Request;
use App\Services\B2B\SellerService;
use App\Http\Controllers\Controller;

class SellerWalletController extends Controller
{

    public function __construct(
        private SellerService $service
    ) {}
    
    public function getEarningReport($userId)
    {
        return $this->service->getEarningReport($userId);
    }
}
