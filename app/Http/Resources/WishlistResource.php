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
            'id' => (int) $this->resource->id,
            'product_id' => $this->resource->product?->id,
            'product_image' => $this->resource->product?->image,
            'product_name' => $this->resource->product?->name,
            'slug' => $this->resource->product?->slug,
            'product_category' => $this->resource->product?->category?->name,
            'product_price' => $this->resource->product?->price,
            'currency' => $this->resource->product?->shopCountry?->currency,
        ];
    }
}
