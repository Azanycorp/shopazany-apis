<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userCurrency = $request->user()->default_currency ?? 'USD';
        $productCurrency = $this->resource->shopCountry->currency ?? 'USD';
        $selectedVariation = $this->resource->productVariations->firstWhere('id', $this->resource->pivot->variation_id);

        return [
            'id' => (int) $this->resource->id,
            'name' => (string) $this->resource->name,
            'description' => (string) $this->resource->description,
            'price' => currencyConvert($productCurrency, $this->resource->pivot->price, $userCurrency),
            'quantity' => (int) $this->resource->pivot->product_quantity,
            'sub_total' => currencyConvert($productCurrency, $this->resource->pivot->sub_total, $userCurrency),
            'original_currency' => (string) $productCurrency,
            'image' => (string) $this->resource->image,
            'status' => (string) $this->resource->pivot->status,
            'variation' => $selectedVariation ? [
                'id' => $selectedVariation->id,
                'variation' => $selectedVariation->variation,
                'sku' => $selectedVariation->sku,
                'price' => currencyConvert(
                    $selectedVariation->product?->shopCountry?->currency,
                    $selectedVariation->price,
                    $userCurrency
                ),
                'image' => $selectedVariation->image,
                'stock' => (int) $selectedVariation->stock,
            ] : null,
        ];
    }
}
