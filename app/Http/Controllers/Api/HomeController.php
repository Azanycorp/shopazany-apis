<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HomeService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $service;

    public function __construct(HomeService $service)
    {
        $this->service = $service;
    }

    public function bestSelling()
    {
        return $this->service->bestSelling();
    }

    public function featuredProduct()
    {
        return $this->service->featuredProduct();
    }

    public function pocketFriendly()
    {
        return $this->service->pocketFriendly();
    }
}
