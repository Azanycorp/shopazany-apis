<?php

namespace App\Services\Cache;

use App\Services\Admin\AdminService;
use App\Services\HomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class FlexibleCacheService extends HomeService
{
    public function __construct(protected AdminService $admin) {}

    public function bestSelling(\Illuminate\Http\Request $request): JsonResponse
    {
        $cacheKey = "best_selling_products:{$request->country_id}:{$request->type}";

        return Cache::flexible($cacheKey, [50, 100], fn () => parent::bestSelling($request));
    }

    public function allProducts(\Illuminate\Http\Request $request): JsonResponse
    {
        $cacheKey = "all_products:{$request->country_id}:{$request->type}";

        return Cache::flexible($cacheKey, [50, 100], fn () => parent::allProducts($request));
    }

    public function featuredProduct(\Illuminate\Http\Request $request): JsonResponse
    {
        $cacheKey = "featured_product:{$request->country_id}:{$request->type}";

        return Cache::flexible($cacheKey, [50, 100], fn () => parent::featuredProduct($request));
    }

    public function topProducts(\Illuminate\Http\Request $request): JsonResponse
    {
        $cacheKey = "top_products:{$request->country_id}:{$request->type}";

        return Cache::flexible($cacheKey, [50, 100], fn () => parent::topProducts($request));
    }

    public function pocketFriendly(\Illuminate\Http\Request $request): JsonResponse
    {
        $cacheKey = "pocket_friendly_products:{$request->country_id}:{$request->type}";

        return Cache::flexible($cacheKey, [50, 100], fn () => parent::pocketFriendly($request));
    }

    public function topSellers(\Illuminate\Http\Request $request): JsonResponse
    {
        $cacheKey = "top_sellers:{$request->country_id}:{$request->type}";

        return Cache::flexible($cacheKey, [50, 100], fn () => parent::topSellers($request));
    }

    public function recommendedProducts(\Illuminate\Http\Request $request): JsonResponse
    {
        $cacheKey = "recommended_products:{$request->country_id}:{$request->type}";

        return Cache::flexible($cacheKey, [50, 100], fn () => parent::recommendedProducts($request));
    }

    public function categories(\Illuminate\Http\Request $request): JsonResponse
    {
        $cacheKey = "categories:{$request->type}";

        return Cache::flexible($cacheKey, [50, 100], fn () => $this->admin->categories($request));
    }
}
