<?php

namespace App\Trait;

use Illuminate\Database\Eloquent\Collection;

trait CartTrait
{
    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cart>  $localItems
     */
    public function getLocalPrice(Collection $localItems, string $defaultCurrency): float
    {
        return $localItems->sum(function (\App\Models\Cart $item) use ($defaultCurrency): float {
            $price = ($item->variation->price ?? $item->product->discounted_price) * $item->quantity;
            $currency = $item->variation
                ? $item->variation->product->shopCountry->currency
                : $item->product?->shopCountry->currency;

            return currencyConvert($currency, $price, $defaultCurrency);
        });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cart>  $internationalItems
     */
    public function getInternaltionalPrice(Collection $internationalItems, string $defaultCurrency): float
    {
        return $internationalItems->sum(function (\App\Models\Cart $item) use ($defaultCurrency): float {
            $price = ($item->variation->price ?? $item->product->discounted_price) * $item->quantity;
            $currency = $item->variation
                ? $item->variation->product->shopCountry->currency
                : $item->product?->shopCountry->currency;

            return currencyConvert($currency, $price, $defaultCurrency);
        });
    }

    /**
     * @return callable(\App\Models\Cart): float
     */
    public function getTotalDiscount(string $defaultCurrency): callable
    {
        return function (\App\Models\Cart $item) use ($defaultCurrency) {
            $original = ((float) $item->product?->product_price) * $item->quantity;
            $discounted = ((float) $item->product?->discounted_price) * $item->quantity;

            $discountAmount = max($original - $discounted, 0);
            $currency = $item->product?->shopCountry?->currency;

            $discountAmount = currencyConvert($currency, $discountAmount, $defaultCurrency);

            return $item->variation ? 0 : $discountAmount;
        };
    }
}
