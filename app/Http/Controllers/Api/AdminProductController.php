<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Services\Admin\ProductService;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminProductController extends Controller
{
    private const MESSAGE = '403 Forbidden';

    public function __construct(
        private readonly ProductService $service,
        private readonly Gate $gate
    ) {}

    public function addProduct(ProductRequest $request)
    {
        abort_if($this->gate->denies('add_new_product'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->addProduct($request);
    }

    public function getProducts(): array
    {
        abort_if($this->gate->denies('product_list'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->getProducts();
    }

    public function getOneProduct($slug)
    {
        abort_if($this->gate->denies('product_list'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->getOneProduct($slug);
    }

    public function changeFeatured(Request $request)
    {
        abort_if($this->gate->denies('product_list'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->changeFeatured($request);
    }
}
