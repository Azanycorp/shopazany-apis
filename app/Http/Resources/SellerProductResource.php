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
            'brand_id' => (string) $this->resource->brand_id,
            'color_id' => (string) $this->resource->color_id,
            'unit_id' => (string) $this->resource->unit_id,
            'size_id' => (string) $this->resource->size_id,
            'brand' => (string) $this->resource->brand?->name,
            'color' => (string) $this->resource->color?->name,
            'unit' => (string) $this->resource->unit?->name,
            'size' => (string) $this->resource->size?->name,
            'product_sku' => (string) $this->resource->product_sku,
            'product_price' => (string) $this->resource->product_price,
            'discount_price' => (string) $this->resource->discount_price,
            'price' => (string) $this->resource->price,
            'current_stock_quantity' => (string) $this->resource->current_stock_quantity,
            'minimum_order_quantity' => (string) $this->resource->minimum_order_quantity,
            'order_count' => (int) $this->resource->orders?->count(),
            'review_count' => (int) $this->resource->productReviews?->count(),
            'rating' => 3.5,
            'front_image' => (string) $this->resource->image,
            'images' => $this->whenLoaded('productimages', function () {
                return $this->resource->productimages->map(function ($image): array {
                    return [
                        'image' => $image->image,
                    ];
                })->toArray();
            }),
            'variations' => $this->resource->productVariations,
            'currency' => $this->resource->shopCountry?->currency,
            'country_id' => (int) $this->resource->country_id,
            'is_featured' => (bool) $this->resource->is_featured,
            'type' => (string) $this->resource->type,
            'status' => (string) $this->resource->status,
        ];
    }
}
