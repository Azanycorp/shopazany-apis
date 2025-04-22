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
        $totalPrice = $pricePerItem * $this->quantity;

        $currency = $this->variation
            ? optional($this->variation->product->shopCountry)->currency
            : optional($this->product->shopCountry)->currency;

        $totalPrice = currencyConvert($currency, $totalPrice, $defaultCurrency);

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
        return $this->variation ? $this->variation->price : optional($this->product)->price;
    }

    private function transformProduct($defaultCurrency): array
    {
        return [
            'id' => optional($this->product)->id,
            'name' => optional($this->product)->name,
            'slug' => optional($this->product)->slug,
            'description' => optional($this->product)->description,
            // 'category' => [
            //     'category_id' => optional($this->product)->category_id,
            //     'category_name' => optional($this->product->category)->name,
            //     'sub_category_id' => optional($this->product)->sub_category_id,
            //     'sub_category_name' => optional($this->product->subCategory)->name,
            // ],
            'product_price' => $this->convertProductPrice($defaultCurrency),
            'discount_price' => $this->convertDiscountPrice($defaultCurrency),
            'price' => $this->convertPrice($defaultCurrency),
            'image' => optional($this->product)->image,
            'brand' => optional($this->product->brand)->name,
            'country_id' => (int)optional($this->product)->country_id,
            'currency' => optional($this->product->shopCountry)->currency,
        ];
    }

    private function transformVariation($defaultCurrency): ?array
    {
        if (!$this->variation) {
            return null;
        }

        return [
            'id' => $this->variation->id,
            'variation' => $this->variation->variation,
            'sku' => $this->variation->sku,
            'price' => (float) currencyConvert(
                optional($this->variation->product->shopCountry)->currency,
                $this->variation->price,
                $defaultCurrency
            ),
            'image' => $this->variation->image,
            'stock' => (int) $this->variation->stock,
        ];
    }

    private function transformSeller(): array
    {
        return [
            'first_name' => optional($this->product)->user?->first_name,
            'last_name' => optional($this->product)->user?->last_name,
        ];
    }

    private function convertProductPrice($defaultCurrency)
    {
        return (float) currencyConvert(
            optional($this->product->shopCountry)->currency,
            optional($this->product) ->product_price,
            $defaultCurrency
        );
    }

    private function convertPrice($defaultCurrency)
    {
        return (float) currencyConvert(
            optional($this->product->shopCountry)->currency,
            $this->product->discounted_price,
            $defaultCurrency
        );
    }

    private function convertDiscountPrice($defaultCurrency)
    {
        return (float) currencyConvert(
            optional($this->product->shopCountry)->currency,
            optional($this->product)->discount_value,
            $defaultCurrency
        );
    }
}

