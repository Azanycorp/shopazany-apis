<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerDetailResource extends JsonResource
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
            'uuid' => (string) $this->resource->uuid,
            'first_name' => (string) $this->resource->first_name,
            'last_name' => (string) $this->resource->last_name,
            'middlename' => (string) $this->resource->middlename,
            'image' => (string) $this->resource->image,
            'product_count' => $this->resource->products_count,
            'products' => $this->resource->products ? $this->resource->products->map(function ($product): array {
                return [
                    'id' => $product->id,
                    'name' => (string) $product->name,
                    'slug' => (string) $product->slug,
                    'description' => (string) $product->description,
                    'category' => (object) [
                        'category_id' => (int) $product?->category_id,
                        'category_name' => (string) $product?->category?->name,
                        'sub_category_id' => (int) $product?->sub_category_id,
                        'sub_category_name' => (string) $product?->subCategory?->name,
                    ],
                    'front_image' => (string) $product?->image,
                    'product_sku' => (string) $product?->product_sku,
                    'product_price' => (string) $product?->product_price,
                    'discount_price' => (string) $product?->discount_price,
                    'price' => (string) $product?->price,
                    'total_reviews' => $product?->product_reviews_count,
                    'item_sold' => $product?->item_sold,
                    'currency' => $product->shopCountry?->currency,
                ];
            })->toArray() : [],
        ];
    }
}
