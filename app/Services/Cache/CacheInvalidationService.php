<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;

class CacheInvalidationService
{
    public function clearHomeServiceCache(int $countryId, string $type): void
    {
        Cache::forget("memo_best_selling_products_{$countryId}_{$type}");
        Cache::forget("memo_all_products_{$countryId}_{$type}");
        Cache::forget("memo_featured_product_{$countryId}_{$type}");
        Cache::forget("memo_top_products_{$countryId}_{$type}");

        $bestSellingKey = "best_selling_products:{$countryId}:{$type}";
        $allProductsKey = "all_products:{$countryId}:{$type}";
        $featuredProductKey = "featured_product:{$countryId}:{$type}";
        $topProductsKey = "top_products:{$countryId}:{$type}";

        $this->invalidateFlexibleCache($bestSellingKey, $allProductsKey, $featuredProductKey, $topProductsKey);
    }

    protected function invalidateFlexibleCache(string $bestSellingKey, string $allProductsKey, string $featuredProductKey, string $topProductsKey): void
    {
        Cache::forget($bestSellingKey);
        Cache::forget($allProductsKey);
        Cache::forget($featuredProductKey);
        Cache::forget($topProductsKey);

        Cache::forget("illuminate:cache:flexible:created:{$bestSellingKey}");
        Cache::forget("illuminate:cache:flexible:created:{$allProductsKey}");
        Cache::forget("illuminate:cache:flexible:created:{$featuredProductKey}");
        Cache::forget("illuminate:cache:flexible:created:{$topProductsKey}");
    }
}
