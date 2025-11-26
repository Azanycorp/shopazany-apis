<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class B2BCategoryResource extends JsonResource
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
            'image' => (string) $this->resource->image,
            'products' => $this->resource->products ? $this->resource->products->map(function ($product): array {
                return [
                    'name' => $product?->name,
                    'category' => $this->resource->name,
                    'image' => $product?->front_image,
                    'slug' => $product?->slug,
                    'sold' => $product?->sold,
                    'price' => (string) $product?->unit_price,
                    'description' => (string) $product?->description,
                    'default_currency' => (string) $product?->default_currency,
                    'keywords' => $product?->keywords,
                    'moq' => (string) $product?->minimum_order_quantity,
                    'status' => (string) $product?->status,
                    'rating' => (int) $product?->b2bProductReview?->avg('rating'),
                    'review_count' => (int) $product->b2b_product_review_count,
                ];
            })->toArray() : [],
            'subcategory' => $this->resource->subcategory ? $this->resource->subcategory->map(function ($subcategory): array {
                return [
                    'name' => $subcategory?->name,
                    'products' => $subcategory->products ? $subcategory->products->map(function ($product): array {
                        return [
                            'name' => $product?->name,
                            'category' => $this->resource->name,
                            'image' => $product?->front_image,
                            'slug' => $product?->slug,
                            'price' => (string) $product?->unit_price,
                            'description' => (string) $product?->description,
                            'default_currency' => (string) $product?->default_currency,
                            'keywords' => $product?->keywords,
                            'moq' => (string) $product?->minimum_order_quantity,
                            'status' => (string) $product?->status,
                            'rating' => (int) $product?->b2bProductReview?->avg('rating'),
                            'review_count' => (int) $product?->b2b_product_review_count,
                        ];
                    })->toArray() : [],
                ];
            })->toArray() : [],
        ];
    }
}
