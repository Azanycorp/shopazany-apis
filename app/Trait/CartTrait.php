<?php

namespace App\Trait;

use Illuminate\Database\Eloquent\Collection;

trait CartTrait
{
    public function getLocalPrice(Collection $localItems, string $defaultCurrency): float
    {
        return $localItems->sum(function ($item) use ($defaultCurrency): float {
            $price = ($item->variation?->price ?? $item->product?->discounted_price) * $item->quantity;
            $currency = $item->variation ? $item->variation?->product?->shopCountry?->currency : $item->product?->shopCountry?->currency;

            return currencyConvert($currency, $price, $defaultCurrency);
        });
    }

    public function getInternaltionalPrice(Collection $internationalItems, string $defaultCurrency): float
    {
        return $internationalItems->sum(function ($item) use ($defaultCurrency): float {
            $price = ($item->variation?->price ?? $item->product?->discounted_price) * $item->quantity;
            $currency = $item->variation ? $item->variation?->product?->shopCountry?->currency : $item->product?->shopCountry?->currency;

            return currencyConvert($currency, $price, $defaultCurrency);
        });
    }

    public function getTotalDiscount(string $defaultCurrency): callable
    {
        return function ($item) use ($defaultCurrency) {
            $original = ($item->product?->product_price) * $item->quantity;
            $discounted = ($item->product?->discounted_price) * $item->quantity;

            $discountAmount = max($original - $discounted, 0);
            $currency = $item->product?->shopCountry?->currency;

            return currencyConvert($currency, $discountAmount, $defaultCurrency);
        };
    }
}
