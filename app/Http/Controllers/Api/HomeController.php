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

    public function productSlug($slug)
    {
        return $this->service->productSlug($slug);
    }

    public function topBrands()
    {
        return $this->service->topBrands();
    }

    public function topSellers()
    {
        return $this->service->topSellers();
    }

    public function categorySlug($slug)
    {
        return $this->service->categorySlug($slug);
    }
}
