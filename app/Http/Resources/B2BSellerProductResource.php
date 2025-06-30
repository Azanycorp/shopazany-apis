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
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'slug' => (string) $this->slug,
            'description' => (string) $this->description,
            'category' => (object) [
                'category_id' => (string) $this->category_id,
                'category_name' => (string) optional($this->category)->name,
                'sub_category_id' => (string) $this->sub_category_id,
                'sub_category_name' => (string) optional($this->subCategory)->name,
            ],
            'price' => (string) $this->unit_price,
            'minimum_order_quantity' => (string) $this->minimum_order_quantity,
            'order_count' => (int) $this->orders?->count(),
            'review_count' => (int) $this->b2bProductReview?->count(),
            'rating' => 3.5,
            'front_image' => (string) $this->front_image,
            'images' => $this->whenLoaded('productimages', function () {
                return $this->productimages->map(function ($image): array {
                    return [
                        'image' => $image->image,
                    ];
                })->toArray();
            }),
            'currency' => $this->shopCountry?->currency,
            'country_id' => (int) $this->country_id,
            'status' => (string) $this->status,
        ];
    }
}
