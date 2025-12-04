<?php

namespace App\Services\Cache;

class CacheWarmService
{
    public function __construct(
        protected FlexibleCacheService $flexibleCacheService
    ) {}

    public function warmHomServiceCache(\Illuminate\Http\Request $request, string $slug): void
    {
        $this->flexibleCacheService->bestSelling($request);
        $this->flexibleCacheService->allProducts($request);
        $this->flexibleCacheService->featuredProduct($request);
        $this->flexibleCacheService->topProducts($request);
        $this->flexibleCacheService->pocketFriendly($request);
        $this->flexibleCacheService->topSellers($request);
        $this->flexibleCacheService->recommendedProducts($request);
        $this->flexibleCacheService->categories($request);
        $this->flexibleCacheService->categorySlug($request, $slug);
    }
}
