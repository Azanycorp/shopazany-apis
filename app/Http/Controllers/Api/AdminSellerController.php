<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\SellerService;
use Illuminate\Http\Request;

class AdminSellerController extends Controller
{
    protected $service;

    public function __construct(SellerService $service)
    {
        $this->service = $service;
    }

    public function allSellers()
    {
        return $this->service->allSellers();
    }
}
