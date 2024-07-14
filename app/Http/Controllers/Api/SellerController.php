<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessInfoRequest;
use App\Services\User\SellerService;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    protected $service;

    public function __construct(SellerService $sellerService)
    {
        $this->service = $sellerService;
    }

    public function businessInfo(BusinessInfoRequest $request)
    {
        return $this->service->businessInfo($request);
    }
}

