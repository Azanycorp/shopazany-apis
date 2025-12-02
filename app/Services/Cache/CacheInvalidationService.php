<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;

class CacheInvalidationService
{
    public function clearHomeServiceCache(int $countryId, string $type, ?string $slug = null): void
    {
        Cache::forget("memo_best_selling_products_{$countryId}_{$type}");
        Cache::forget("memo_all_products_{$countryId}_{$type}");
        Cache::forget("memo_featured_product_{$countryId}_{$type}");
        Cache::forget("memo_top_products_{$countryId}_{$type}");
        Cache::forget("memo_top_sellers_{$countryId}_{$type}");
        Cache::forget("memo_recommended_products_{$countryId}_{$type}");
        Cache::forget("memo_categories_{$type}");
        Cache::forget("memo_category_slug_{$countryId}_{$slug}");
        Cache::forget("memo_pocket_friendly_products_{$countryId}_{$type}");

        $bestSellingKey = "best_selling_products:{$countryId}:{$type}";
        $allProductsKey = "all_products:{$countryId}:{$type}";
        $featuredProductKey = "featured_product:{$countryId}:{$type}";
        $topProductsKey = "top_products:{$countryId}:{$type}";
        $topSellersKey = "top_sellers:{$countryId}:{$type}";
        $recommendedProductsKey = "recommended_products:{$countryId}:{$type}";
        $categoriesKey = "categories:{$type}";
        $categorySlugKey = "category_slug:{$countryId}:{$slug}";
        $pocketFriendlyKey = "pocket_friendly_products:{$countryId}:{$type}";

        $keys = [
            $bestSellingKey,
            $allProductsKey,
            $featuredProductKey,
            $topProductsKey,
            $topSellersKey,
            $recommendedProductsKey,
            $categoriesKey,
            $categorySlugKey,
            $pocketFriendlyKey,
        ];

        $this->invalidateFlexibleCache($keys);
    }

    protected function invalidateFlexibleCache(array $keys): void
    {
        foreach ($keys as $key) {
            Cache::forget($key);
            Cache::forget("illuminate:cache:flexible:created:{$key}");
        }
    }
}
