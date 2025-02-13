<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MoveToCartRequest;
use App\Http\Requests\ProductReviewRequest;
use App\Services\HomeService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected \App\Services\HomeService $service;

    public function __construct(HomeService $service)
    {
        $this->service = $service;
    }

    public function bestSelling()
    {
        return $this->service->bestSelling();
    }

    public function allProducts(): array
    {
        return $this->service->allProducts();
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

    public function recommendedProducts()
    {
        return $this->service->recommendedProducts();
    }

    public function productReview(ProductReviewRequest $request)
    {
        return $this->service->productReview($request);
    }

    public function saveForLater(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'product_id' => ['required', 'integer']
        ]);

        return $this->service->saveForLater($request);
    }

    public function sellerInfo($uuid)
    {
        return $this->service->sellerInfo($uuid);
    }

    public function sellerCategory($uuid)
    {
        return $this->service->sellerCategory($uuid);
    }

    public function sellerReviews($uuid)
    {
        return $this->service->sellerReviews($uuid);
    }

    public function moveToCart(MoveToCartRequest $request)
    {
        return $this->service->moveToCart($request);
    }

    public function topProducts()
    {
        return $this->service->topProducts();
    }
}
