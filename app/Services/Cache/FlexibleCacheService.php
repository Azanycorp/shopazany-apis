<?php

namespace App\Services\Cache;

use App\Services\Admin\AdminService;
use App\Services\HomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FlexibleCacheService extends HomeService
{
    public function __construct(protected AdminService $admin) {}

    public function bestSelling(Request $request): JsonResponse
    {
        $cacheKey = "best_selling_products:{$request->country_id}:{$request->type}";

        return Cache::flexible($cacheKey, [200, 300], fn () => parent::bestSelling($request));
    }

    public function allProducts(Request $request): JsonResponse
    {
        $cacheKey = "all_products:{$request->country_id}:{$request->type}";

        return Cache::flexible($cacheKey, [200, 300], fn () => parent::allProducts($request));
    }

    public function featuredProduct(Request $request): JsonResponse
    {
        $cacheKey = "featured_product:{$request->country_id}:{$request->type}";

        return Cache::flexible($cacheKey, [200, 300], fn () => parent::featuredProduct($request));
    }

    public function topProducts(Request $request): JsonResponse
    {
        $cacheKey = "top_products:{$request->country_id}:{$request->type}";

        return Cache::flexible($cacheKey, [200, 300], fn () => parent::topProducts($request));
    }

    public function pocketFriendly(Request $request): JsonResponse
    {
        $cacheKey = "pocket_friendly_products:{$request->country_id}:{$request->type}";

        return Cache::flexible($cacheKey, [200, 300], fn () => parent::pocketFriendly($request));
    }

    public function topSellers(Request $request): JsonResponse
    {
        $cacheKey = "top_sellers:{$request->country_id}:{$request->type}";

        return Cache::flexible($cacheKey, [200, 300], fn () => parent::topSellers($request));
    }

    public function recommendedProducts(Request $request): JsonResponse
    {
        $cacheKey = "recommended_products:{$request->country_id}:{$request->type}";

        return Cache::flexible($cacheKey, [200, 300], fn () => parent::recommendedProducts($request));
    }

    public function categories(Request $request): JsonResponse
    {
        $cacheKey = "categories:{$request->type}";

        return Cache::flexible($cacheKey, [200, 300], fn () => $this->admin->categories($request));
    }

    public function categorySlug(Request $request, string $slug): JsonResponse
    {
        $cacheKey = "category_slug:{$request->country_id}:{$slug}";

        return Cache::flexible($cacheKey, [200, 300], fn () => parent::categorySlug($request, $slug));
    }
}
