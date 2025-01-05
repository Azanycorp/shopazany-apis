<?php

namespace App\Http\Controllers\Api\B2B\Buyer;

use Illuminate\Http\Request;
use App\Services\B2B\SellerService;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function __construct(
        private SellerService $sellerService
    ) {}

    public function profile()
    {
        return $this->sellerService->profile();
    }
}
