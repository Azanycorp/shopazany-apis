<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pricePerItem = $this->calculatePrice();
        $totalPrice = $pricePerItem * $this->quantity;

        return [
            'id' => (int) $this->id,
            'quantity' => (int) $this->quantity,
            'product' => $this->transformProduct(),
            'seller' => $this->transformSeller(),
            'total_price' => $totalPrice,
        ];
    }

    private function calculatePrice()
    {
        return optional($this->product)->price;
    }

    private function transformProduct(): array
    {
        return [
            'id' => optional($this->product)->id,
            'name' => optional($this->product)->name,
            'slug' => optional($this->product)->slug,
            'description' => optional($this->product)->description,
            'category' => [
                'category_id' => optional($this->product)->category_id,
                'category_name' => optional($this->product->category)->name,
                'sub_category_id' => optional($this->product)->sub_category_id,
                'sub_category_name' => optional($this->product->subCategory)->name,
            ],
            'product_price' => (int) optional($this->product)->product_price,
            'discount_price' => (int) optional($this->product)->discount_price,
            'price' => (int) optional($this->product)->price,
            'image' => optional($this->product)->image,
            'color' => optional($this->product->color)->name,
            'size' => optional($this->product->size)->name,
            'unit' => optional($this->product->unit)->name,
            'brand' => optional($this->product->brand)->name,
            'country_id' => (int)optional($this->product)->country_id,
            'currency' => optional($this->product->shopCountry)->currency,
        ];
    }

    private function transformSeller(): array
    {
        return [
            'first_name' => optional($this->product)->user?->first_name,
            'last_name' => optional($this->product)->user?->last_name,
        ];
    }
}

