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
        $userCurrency = $request->user()?->default_currency ?? 'USD';
        $productCurrency = optional($this->shopCountry)->currency ?? 'USD';
        $selectedVariation = $this->productVariations->firstWhere('id', $this->pivot->variation_id);

        return [
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'description' => (string) $this->description,
            'price' => (float) $this->pivot->price,
            'quantity' => (int) $this->pivot->product_quantity,
            'sub_total' => currencyConvert($productCurrency, $this->pivot->sub_total, $userCurrency),
            'original_currency' => (string) $productCurrency,
            'image' => (string) $this->image,
            'status' => (string) $this->pivot->status,
            'variation' => $selectedVariation ? [
                'id' => $selectedVariation->id,
                'variation' => $selectedVariation->variation,
                'sku' => $selectedVariation->sku,
                'price' => currencyConvert(
                    optional($selectedVariation->product->shopCountry)->currency,
                    $selectedVariation->price,
                    $userCurrency
                ),
                'image' => $selectedVariation->image,
                'stock' => (int) $selectedVariation->stock,
            ] : null,
        ];
    }
}
