<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\Cache\CacheInvalidationService;

class ProductObserver
{
    public function __construct(
        private CacheInvalidationService $cacheInvalidationService,
    ) {}

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->cacheInvalidationService->clearHomeServiceCache($product->country_id, $product->type, $product?->category->slug);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        if ($product->isDirty(['name', 'product_price', 'status'])) {
            $this->created($product);
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $this->created($product);
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        $this->created($product);
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        $this->created($product);
    }
}
