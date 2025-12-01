<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class B2BSellerProductResource extends JsonResource
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
            'name' => (string) $this->resource->name,
            'slug' => (string) $this->resource->slug,
            'description' => (string) $this->resource->description,
            'category' => (object) [
                'category_id' => (string) $this->resource->category_id,
                'category_name' => (string) $this->resource->category?->name,
                'sub_category_id' => (string) $this->resource->sub_category_id,
                'sub_category_name' => (string) $this->resource->subCategory?->name,
            ],
            'price' => (string) $this->resource->unit_price,
            'minimum_order_quantity' => (string) $this->resource->minimum_order_quantity,
            'order_count' => (int) $this->resource->orders?->count(),
            'review_count' => (int) $this->resource->b2bProductReview?->count(),
            'rating' => 3.5,
            'front_image' => (string) $this->resource->front_image,
            'images' => $this->whenLoaded('productimages', function () {
                return $this->resource->productimages->map(function ($image): array {
                    return [
                        'image' => $image->image,
                    ];
                })->toArray();
            }),
            'currency' => $this->resource->shopCountry?->currency,
            'country_id' => (int) $this->resource->country_id,
            'status' => (string) $this->resource->status,
        ];
    }
}
