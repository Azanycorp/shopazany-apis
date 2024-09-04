<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Services\Admin\ProductService;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    protected $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    public function addProduct(ProductRequest $request)
    {
        return $this->service->addProduct($request);
    }

    public function getProduct()
    {
        return $this->service->getProduct();
    }

    public function getOneProduct($slug)
    {
        return $this->service->getOneProduct($slug);
    }
}
