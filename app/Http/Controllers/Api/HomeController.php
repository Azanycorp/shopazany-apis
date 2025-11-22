<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MoveToCartRequest;
use App\Http\Requests\ProductReviewRequest;
use App\Services\HomeService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(
        protected HomeService $service
    ) {}

    public function bestSelling(Request $request)
    {
        return $this->service->bestSelling($request);
    }

    public function allProducts(Request $request)
    {
        return $this->service->allProducts($request);
    }

    public function featuredProduct(Request $request)
    {
        return $this->service->featuredProduct($request);
    }

    public function pocketFriendly(Request $request)
    {
        return $this->service->pocketFriendly($request);
    }

    public function productSlug($slug)
    {
        return $this->service->productSlug($slug);
    }

    public function topBrands()
    {
        return $this->service->topBrands();
    }

    public function topSellers(Request $request)
    {
        return $this->service->topSellers($request);
    }

    public function categorySlug(Request $request, $slug)
    {
        return $this->service->categorySlug($request, $slug);
    }

    public function recommendedProducts(Request $request)
    {
        return $this->service->recommendedProducts($request);
    }

    public function productReview(ProductReviewRequest $request)
    {
        return $this->service->productReview($request);
    }

    public function saveForLater(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'product_id' => ['required', 'integer'],
        ]);

        return $this->service->saveForLater($request);
    }

    public function sellerInfo(Request $request, $uuid)
    {
        return $this->service->sellerInfo($request, $uuid);
    }

    public function sellerCategory(Request $request, $uuid)
    {
        return $this->service->sellerCategory($request, $uuid);
    }

    public function sellerReviews(Request $request, $uuid)
    {
        return $this->service->sellerReviews($request, $uuid);
    }

    public function moveToCart(MoveToCartRequest $request)
    {
        return $this->service->moveToCart($request);
    }

    public function topProducts(Request $request)
    {
        return $this->service->topProducts($request);
    }

    public function flashDeals()
    {
        return $this->service->flashDeals();
    }

    public function singleFlashDeal($slug)
    {
        return $this->service->singleFlashDeal($slug);
    }

    public function getDeals(Request $request)
    {
        return $this->service->getDeals($request);
    }

    public function getDealDetail($slug)
    {
        return $this->service->getDealDetail($slug);
    }
}
