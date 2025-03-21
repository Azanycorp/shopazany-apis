<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\B2BProductResource;
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
            'id' => (int)$this->id,
            'name' => (string)$this->name,
            'slug' => (string)$this->slug,
            'image' => (string)$this->image,
            'products' => $this->products ? $this->products->map(function ($product): array {
                return [
                    'name' => $product?->name,
                    'category' => $this->name,
                    'image' => $product?->front_image,
                    'slug' => $product?->slug,
                    'price' => (string)$product?->unit_price,
                    'description' => (string)$product?->description,
                    'default_currency' => (string)$product?->default_currency,
                    'keywords' => $product?->keywords,
                    'moq' => (string)$product?->minimum_order_quantity,
                    'status' => (string)$product?->status,
                    'rating' => (int)$product?->b2bProductReview?->avg('rating'),
                    'review_count' => (int)$product->b2b_product_review_count,
                ];
            })->toArray() : [],
            'subcategory' => $this->subcategory ? $this->subcategory->map(function ($subcategory): array {
                return [
                    'name' => $subcategory?->name,
                    'products' => $this->products ? $this->products->map(function ($product): array {
                        return [
                            'name' => $product?->name,
                            'category' => $this->name,
                            'image' => $product?->front_image,
                            'price' => (string)$product?->unit_price,
                            'description' => (string)$product?->description,
                            'default_currency' => (string)$product?->default_currency,
                            'keywords' => $product?->keywords,
                            'moq' => (string)$product?->minimum_order_quantity,
                            'status' => (string)$product?->status,
                            'rating' => (int)$product?->b2bProductReview?->avg('rating'),
                            'review_count' => (int)$product?->b2b_product_review_count,
                        ];
                    })->toArray() : [],
                ];
            })->toArray() : [],
        ];
    }
}
