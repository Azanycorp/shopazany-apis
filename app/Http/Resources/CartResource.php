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
        $price = $pricePerItem * $this->resource->quantity;

        $currency = $this->resource->variation
            ? $this->resource->variation->product?->shopCountry?->currency
            : $this->resource->product?->shopCountry?->currency;

        $totalPrice = currencyConvert($currency, $price, $defaultCurrency);

        return [
            'id' => (int) $this->resource->id,
            'quantity' => (int) $this->resource->quantity,
            'variation' => $this->transformVariation($defaultCurrency),
            'product' => $this->transformProduct($defaultCurrency),
            'seller' => $this->transformSeller(),
            'total_price' => $totalPrice,
        ];
    }

    private function calculatePrice()
    {
        return $this->resource->variation ? $this->resource->variation->price : $this->resource->product?->discounted_price;
    }

    private function transformProduct($defaultCurrency): array
    {
        return [
            'id' => $this->resource->product?->id,
            'name' => $this->resource->product?->name,
            'slug' => $this->resource->product?->slug,
            'description' => $this->resource->product?->description,
            'product_price' => $this->convertProductPrice($defaultCurrency),
            'discount_price' => $this->convertDiscountPrice($defaultCurrency),
            'price' => $this->convertPrice($defaultCurrency),
            'image' => $this->resource->product?->image,
            'brand' => $this->resource->product?->brand?->name,
            'country_id' => (int) $this->resource->product?->country_id,
            'currency' => $this->resource->product?->shopCountry?->currency,
        ];
    }

    private function transformVariation($defaultCurrency): ?array
    {
        if (! $this->resource->variation) {
            return null;
        }

        return [
            'id' => $this->resource->variation->id,
            'variation' => $this->resource->variation->variation,
            'sku' => $this->resource->variation->sku,
            'price' => currencyConvert(
                $this->resource->variation->product?->shopCountry?->currency,
                $this->resource->variation->price,
                $defaultCurrency
            ),
            'image' => $this->resource->variation->image,
            'stock' => (int) $this->resource->variation->stock,
        ];
    }

    private function transformSeller(): array
    {
        return [
            'first_name' => $this->resource->product?->user?->first_name,
            'last_name' => $this->resource->product?->user?->last_name,
        ];
    }

    private function convertProductPrice($defaultCurrency): float
    {
        return currencyConvert(
            $this->resource->product?->shopCountry?->currency,
            $this->resource->product?->product_price,
            $defaultCurrency
        );
    }

    private function convertPrice($defaultCurrency): float
    {
        return currencyConvert(
            $this->resource->product?->shopCountry?->currency,
            $this->resource->product?->discounted_price,
            $defaultCurrency
        );
    }

    private function convertDiscountPrice($defaultCurrency): float
    {
        return currencyConvert(
            $this->resource->product?->shopCountry?->currency,
            $this->resource->product?->discount_value,
            $defaultCurrency
        );
    }
}
