<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class B2BProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $average_rating = $this->resource->b2bProductReview->avg('rating');

        return [
            'id' => (int) $this->resource->id,
            'name' => (string) $this->resource->name,
            'slug' => (string) $this->resource->slug,
            'category' => (string) $this->resource->category?->name,
            'subCategory' => (string) $this->resource->subCategory?->name,
            'price' => (string) $this->resource->unit_price,
            'sold' => (int) $this->resource->sold,
            'front_image' => (string) $this->resource->front_image,
            'description' => (string) $this->resource->description,
            'vendor' => UserResource::make($this->resource->user),
            'quantity' => (string) $this->resource->quantity,
            'default_currency' => (string) $this->resource->default_currency,
            'availability_quantity' => (string) $this->resource->availability_quantity,
            'admin_comment' => (string) $this->resource->admin_comment,
            'keywords' => $this->resource?->keywords,
            'moq' => (string) $this->resource->minimum_order_quantity,
            'status' => (string) $this->resource->status,
            'rating' => floatval($average_rating),
            'country' => (string) $this->resource->country?->name,
            'reviews' => $this->resource->b2bProductReview ? $this->resource->b2bProductReview->map(function ($b2bProductReview): array {
                return [
                    'buyer' => $b2bProductReview->user?->buyerName,
                    'rating' => floatval($b2bProductReview->rating),
                    'title' => $b2bProductReview->title,
                    'note' => $b2bProductReview->title,
                ];
            })->toArray() : [],
            'review_count' => (int) $this->resource->b2bProductReview?->count(),
            'b2bLikes' => $this->resource->b2bLikes->count(),
            'images' => $this->resource->b2bProductImages ? $this->resource->b2bProductImages->map(function ($image): array {
                return [
                    'image' => $image->image,
                ];
            })->toArray() : [],
            'date' => (string) $this->resource->created_at,
        ];
    }
}
