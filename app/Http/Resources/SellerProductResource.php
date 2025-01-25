<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerProductResource extends JsonResource
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
            'name' => (string)$this->name,
            'slug' => (string)$this->slug,
            'description' => (string)$this->description,
            'category' => (object) [
                'category_id' => (string)$this->category_id,
                'category_name' => (string)optional($this->category)->name,
                'sub_category_id' => (string)$this->sub_category_id,
                'sub_category_name' => (string)optional($this->subCategory)->name,
            ],
            'brand_id' => (string)$this->brand_id,
            'color_id' => (string)$this->color_id,
            'unit_id' => (string)$this->unit_id,
            'size_id' => (string)$this->size_id,
            'brand' => (string)$this->brand?->name,
            'color' => (string)$this->color?->name,
            'unit' => (string)$this->unit?->name,
            'size' => (string)$this->size?->name,
            'product_sku' => (string)$this->product_sku,
            'product_price' => (string)$this->product_price,
            'discount_price' => (string)$this->discount_price,
            'price' => (string)$this->price,
            'current_stock_quantity' => (string)$this->current_stock_quantity,
            'minimum_order_quantity' => (string)$this->minimum_order_quantity,
            'order_count' => (int)$this->orders?->count(),
            'review_count' => (int)$this->productReviews?->count(),
            'rating' => 3.5,
            'front_image' => (string)$this->image,
            'images' => $this->whenLoaded('productimages', function () {
                return $this->productimages->map(function ($image) {
                    return [
                        'image' => $image->image
                    ];
                })->toArray();
            }),
            'currency' => $this->shopCountry?->currency,
            'country_id' => (int)$this->country_id,
            'status' => (string)$this->status
        ];
    }
}
