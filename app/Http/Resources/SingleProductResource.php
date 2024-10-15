<?php

namespace App\Http\Resources;

use App\Enum\OrderStatus;
use App\Models\Order;
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
        $this->load('productimages', 'user.userCountry');
        $item_sold = Order::where('product_id', $this->id)
        ->where('status', OrderStatus::DELIVERED)
        ->count();

        return [
            'id' => (int)$this->id,
            'name' => (string)$this->name,
            'slug' => (string)$this->slug,
            'description' => (string)$this->description,
            'category' => (object) [
                'category_id' => (int)$this->category_id,
                'category_name' => (string)optional($this->category)->name,
                'sub_category_id' => (int)$this->sub_category_id,
                'sub_category_name' => (string)optional($this->subCategory)->name,
            ],
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
            'front_image' => (string)$this->image,
            'images' => $this->whenLoaded('productimages', function () {
                return $this->productimages->map(function ($image) {
                    return [
                        'image' => $image->image
                    ];
                })->toArray();
            }),
            'reviews' => $this->productReviews ? $this->productReviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'review' => $review->review,
                ];
            })->toArray() : [],
            'total_reviews' => $this->product_reviews_count,
            'item_sold' => $item_sold,
            'seller' => $this->whenLoaded('user', function () {
                return (object) [
                    'id' => optional($this->user)->id,
                    'uuid' => optional($this->user)->uuid,
                    'name' => $this->user->first_name . ' '. optional($this->user)->last_name,
                    'country' => optional($this->user)->userCountry?->name,
                ];
            }),
        ];
    }
}
