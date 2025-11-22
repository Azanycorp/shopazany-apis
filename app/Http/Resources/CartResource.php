<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $defaultCurrency = userAuth()->default_currency;

        $pricePerItem = $this->calculatePrice();
        $price = $pricePerItem * $this->quantity;

        $currency = $this->variation
            ? $this->variation?->product?->shopCountry?->currency
            : $this->product?->shopCountry?->currency;

        $totalPrice = currencyConvert($currency, $price, $defaultCurrency);

        return [
            'id' => (int) $this->id,
            'quantity' => (int) $this->quantity,
            'variation' => $this->transformVariation($defaultCurrency),
            'product' => $this->transformProduct($defaultCurrency),
            'seller' => $this->transformSeller(),
            'total_price' => $totalPrice,
        ];
    }

    private function calculatePrice()
    {
        return $this->variation ? $this->variation->price : $this->product?->discounted_price;
    }

    private function transformProduct($defaultCurrency): array
    {
        return [
            'id' => $this->product?->id,
            'name' => $this->product?->name,
            'slug' => $this->product?->slug,
            'description' => $this->product?->description,
            'product_price' => $this->convertProductPrice($defaultCurrency),
            'discount_price' => $this->convertDiscountPrice($defaultCurrency),
            'price' => $this->convertPrice($defaultCurrency),
            'image' => $this->product?->image,
            'brand' => $this->product?->brand?->name,
            'country_id' => (int) $this->product?->country_id,
            'currency' => $this->product?->shopCountry?->currency,
        ];
    }

    private function transformVariation($defaultCurrency): ?array
    {
        if (! $this->variation) {
            return null;
        }

        return [
            'id' => $this->variation->id,
            'variation' => $this->variation->variation,
            'sku' => $this->variation->sku,
            'price' => currencyConvert(
                $this->variation?->product?->shopCountry?->currency,
                $this->variation?->price,
                $defaultCurrency
            ),
            'image' => $this->variation?->image,
            'stock' => (int) $this->variation->stock,
        ];
    }

    private function transformSeller(): array
    {
        return [
            'first_name' => $this->product?->user?->first_name,
            'last_name' => $this->product?->user?->last_name,
        ];
    }

    private function convertProductPrice($defaultCurrency): float
    {
        return currencyConvert(
            $this->product?->shopCountry?->currency,
            $this->product?->product_price,
            $defaultCurrency
        );
    }

    private function convertPrice($defaultCurrency): float
    {
        return currencyConvert(
            $this->product?->shopCountry?->currency,
            $this->product?->discounted_price,
            $defaultCurrency
        );
    }

    private function convertDiscountPrice($defaultCurrency): float
    {
        return currencyConvert(
            $this->product?->shopCountry?->currency,
            $this->product?->discount_value,
            $defaultCurrency
        );
    }
}
