<?php

namespace App\Trait;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Collection;

trait CartTrait
{
    /**
     * @param  Collection<int, Cart>  $localItems
     */
    public function getLocalPrice(Collection $localItems, string $defaultCurrency): float
    {
        return $localItems->sum(function (Cart $item) use ($defaultCurrency): float {
            $unitPrice = $item->variation->price
                ?? $item->product->discounted_price
                ?? 0;

            $price = $unitPrice * $item->quantity;

            $product = $item->variation
                ? $item->variation->product
                : $item->product;

            if (! $product || ! $product->shopCountry) {
                return 0;
            }

            $currency = $product->shopCountry->currency;

            if (! $currency) {
                return 0;
            }

            return currencyConvert($currency, $price, $defaultCurrency);
        });
    }

    /**
     * @param  Collection<int, Cart>  $internationalItems
     */
    public function getInternaltionalPrice(Collection $internationalItems, string $defaultCurrency): float
    {
        return $internationalItems->sum(function (Cart $item) use ($defaultCurrency): float {
            $unitPrice = $item->variation->price
                ?? $item->product->discounted_price
                ?? 0;

            $price = $unitPrice * $item->quantity;

            $product = $item->variation
                ? $item->variation->product
                : $item->product;

            if (! $product || ! $product->shopCountry) {
                return 0;
            }

            $currency = $product->shopCountry->currency;

            if (! $currency) {
                return 0;
            }

            return currencyConvert($currency, $price, $defaultCurrency);
        });
    }

    /**
     * @return callable(Cart): float
     */
    public function getTotalDiscount(string $defaultCurrency): callable
    {
        return function (Cart $item) use ($defaultCurrency) {
            $original = ((float) $item->product?->product_price) * $item->quantity;
            $discounted = ((float) $item->product?->discounted_price) * $item->quantity;

            $discountAmount = max($original - $discounted, 0);
            $currency = $item->product?->shopCountry?->currency;

            $discountAmount = currencyConvert($currency, $discountAmount, $defaultCurrency);

            return $item->variation ? 0 : $discountAmount;
        };
    }
}
