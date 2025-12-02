<?php

namespace App\Services\Cache;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class MemoizedCacheService
{
    public function __construct(
        protected FlexibleCacheService $flexibleCacheService
    ) {}

    public function bestSelling(\Illuminate\Http\Request $request): JsonResponse
    {
        $memoKey = "memo_best_selling_products_{$request->country_id}_{$request->type}";

        return Cache::memo()->remember($memoKey, 3600, fn () => $this->flexibleCacheService->bestSelling($request));
    }

    public function allProducts(\Illuminate\Http\Request $request): JsonResponse
    {
        $memoKey = "memo_all_products_{$request->country_id}_{$request->type}";

        return Cache::memo()->remember($memoKey, 3600, fn () => $this->flexibleCacheService->allProducts($request));
    }

    public function featuredProduct(\Illuminate\Http\Request $request): JsonResponse
    {
        $memoKey = "memo_featured_product_{$request->country_id}_{$request->type}";

        return Cache::memo()->remember($memoKey, 3600, fn () => $this->flexibleCacheService->featuredProduct($request));
    }

    public function topProducts(\Illuminate\Http\Request $request): JsonResponse
    {
        $memoKey = "memo_top_products_{$request->country_id}_{$request->type}";

        return Cache::memo()->remember($memoKey, 3600, fn () => $this->flexibleCacheService->topProducts($request));
    }

    public function pocketFriendly(\Illuminate\Http\Request $request): JsonResponse
    {
        $memoKey = "memo_pocket_friendly_products_{$request->country_id}_{$request->type}";

        return Cache::memo()->remember($memoKey, 3600, fn () => $this->flexibleCacheService->pocketFriendly($request));
    }

    public function topSellers(\Illuminate\Http\Request $request): JsonResponse
    {
        $memoKey = "memo_top_sellers_{$request->country_id}_{$request->type}";

        return Cache::memo()->remember($memoKey, 3600, fn () => $this->flexibleCacheService->topSellers($request));
    }

    public function recommendedProducts(\Illuminate\Http\Request $request): JsonResponse
    {
        $memoKey = "memo_recommended_products_{$request->country_id}_{$request->type}";

        return Cache::memo()->remember($memoKey, 3600, fn () => $this->flexibleCacheService->recommendedProducts($request));
    }

    public function categories(\Illuminate\Http\Request $request): JsonResponse
    {
        $memoKey = "memo_categories_{$request->type}";

        return Cache::memo()->remember($memoKey, 3600, fn () => $this->flexibleCacheService->categories($request));
    }
}
