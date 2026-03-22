<?php

namespace App\Trait;

use App\Models\Product;
use Illuminate\Support\Str;

trait GeneratesSku
{
    public function generateUniqueSku(?string $prefix = 'SKU'): string
    {
        do {
            $sku = strtoupper($prefix.'-'.Str::random(6).'-'.now()->format('ymd'));
        } while ($this->skuExists($sku));

        return $sku;
    }

    protected function skuExists(string $sku): bool
    {
        return Product::where('product_sku', $sku)->exists();
    }
}
