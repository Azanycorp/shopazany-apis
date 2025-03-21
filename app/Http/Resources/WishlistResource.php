<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int)$this->id,
            'product_id' => $this->product?->id,
            'product_image' => $this->product?->image,
            'product_name' => $this->product?->name,
            'slug' => $this->product?->slug,
            'product_category' => $this->product?->category?->name,
            'product_price' => $this->product?->price,
            'currency' => $this->product?->shopCountry?->currency,
        ];
    }
}
