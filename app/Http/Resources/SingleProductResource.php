<?php

namespace App\Http\Resources;

use App\Enum\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SingleProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $item_sold = Order::whereHas('products', function (Builder $query): void {
            $query->where('product_id', $this->resource->id);
        })
            ->where('status', OrderStatus::DELIVERED)
            ->count();

        $average_rating = $this->resource->productReviews->avg('rating');

        return [
            'id' => (int) $this->resource->id,
            'name' => (string) $this->resource->name,
            'slug' => (string) $this->resource->slug,
            'description' => (string) $this->resource->description,
            'category' => (object) [
                'category_id' => (int) $this->resource->category_id,
                'category_name' => (string) $this->resource->category?->name,
                'sub_category_id' => (int) $this->resource->sub_category_id,
                'sub_category_name' => (string) $this->resource->subCategory?->name,
            ],
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
            'front_image' => (string) $this->resource->image,
            'currency' => $this->resource->shopCountry?->currency,
            'country_id' => (int) $this->resource->country_id,
            'is_in_wishlist' => (bool) $this->resource->is_in_wishlist,
            'images' => $this->whenLoaded('productimages', function () {
                return $this->resource->productimages->map(function ($image): array {
                    return [
                        'image' => $image->image,
                    ];
                })->toArray();
            }),
            'reviews' => $this->resource->productReviews ? $this->resource->productReviews->map(function ($review): array {
                return [
                    'id' => $review->id,
                    'user' => $review?->user?->full_name,
                    'rating' => $review->rating,
                    'review' => $review->review,
                    'date' => $review->created_at,
                ];
            })->toArray() : [],
            'variations' => $this->resource->productVariations,
            'total_reviews' => $this->resource->product_reviews_count,
            'average_rating' => round($average_rating, 1),
            'item_sold' => $item_sold,
            'seller' => (object) [
                'id' => $this->resource->user?->id,
                'uuid' => $this->resource->user?->uuid,
                'name' => "{$this->resource->user?->first_name} {$this->resource->user?->last_name}",
                'flag' => $this->resource->user?->userCountry?->shopCountry?->flag,
                'country' => $this->resource->user?->userCountry?->name,
            ],
        ];
    }
}
