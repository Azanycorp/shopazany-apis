<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\B2B\BuyerService;

class B2BBuyerController extends Controller
{
    protected $buyerService;

    public function __construct(BuyerService $buyerService)
    {
        $this->buyerService = $buyerService;
    }

    public function requestRefund(Request $request)
    {
        return $this->buyerService->requestRefund($request);
    }
}
