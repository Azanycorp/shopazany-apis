<?php

namespace App\Services\Cache;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MemoizedCacheService
{
    public function __construct(
        protected FlexibleCacheService $flexibleCacheService
    ) {}

    public function bestSelling(Request $request): JsonResponse
    {
        $memoKey = "memo_best_selling_products_{$request->country_id}_{$request->type}";

        return Cache::memo()->remember($memoKey, 3600, fn () => $this->flexibleCacheService->bestSelling($request));
    }

    public function allProducts(Request $request): JsonResponse
    {
        $memoKey = "memo_all_products_{$request->country_id}_{$request->type}";

        return Cache::memo()->remember($memoKey, 3600, fn () => $this->flexibleCacheService->allProducts($request));
    }

    public function featuredProduct(Request $request): JsonResponse
    {
        $memoKey = "memo_featured_product_{$request->country_id}_{$request->type}";

        return Cache::memo()->remember($memoKey, 3600, fn () => $this->flexibleCacheService->featuredProduct($request));
    }

    public function topProducts(Request $request): JsonResponse
    {
        $memoKey = "memo_top_products_{$request->country_id}_{$request->type}";

        return Cache::memo()->remember($memoKey, 3600, fn () => $this->flexibleCacheService->topProducts($request));
    }

    public function pocketFriendly(Request $request): JsonResponse
    {
        $memoKey = "memo_pocket_friendly_products_{$request->country_id}_{$request->type}";

        return Cache::memo()->remember($memoKey, 3600, fn () => $this->flexibleCacheService->pocketFriendly($request));
    }

    public function topSellers(Request $request): JsonResponse
    {
        $memoKey = "memo_top_sellers_{$request->country_id}_{$request->type}";

        return Cache::memo()->remember($memoKey, 3600, fn () => $this->flexibleCacheService->topSellers($request));
    }

    public function recommendedProducts(Request $request): JsonResponse
    {
        $memoKey = "memo_recommended_products_{$request->country_id}_{$request->type}";

        return Cache::memo()->remember($memoKey, 3600, fn () => $this->flexibleCacheService->recommendedProducts($request));
    }

    public function categories(Request $request): JsonResponse
    {
        $memoKey = "memo_categories_{$request->type}";

        return Cache::memo()->remember($memoKey, 3600, fn () => $this->flexibleCacheService->categories($request));
    }

    public function categorySlug(Request $request, string $slug): JsonResponse
    {
        $memoKey = "memo_category_slug_{$request->country_id}_{$slug}";

        return Cache::memo()->remember($memoKey, 3600, fn () => $this->flexibleCacheService->categorySlug($request, $slug));
    }
}
