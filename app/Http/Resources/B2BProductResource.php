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
        $average_rating = $this->b2bProductReview->avg('rating');

        return [
            'id' => (int)$this->id,
            'name' => (string)$this->name,
            'slug' => (string)$this->slug,
            'category' => (string)$this->category?->name,
            'subCategory' => (string)$this->subCategory?->name,
            'price' => (string)$this->unit_price,
            'sold' => (int)$this->sold,
            'front_image' => (string)$this->front_image,
            'description' => (string)$this->description,
            'vendor' => UserResource::make($this->user),
            'quantity' => (string)$this->quantity,
            'default_currency' => (string)$this->default_currency,
            'availability_quantity' => (string)$this->availability_quantity,
            'admin_comment' => (string)$this->admin_comment,
            'keywords' => $this?->keywords,
            'moq' => (string)$this->minimum_order_quantity,
            'status' => (string)$this->status,
            'rating' => floatval($average_rating),
            'country' => (string)$this->country?->name,
            'reviews' => $this->b2bProductReview ? $this->b2bProductReview->map(function ($b2bProductReview): array {
                return [
                    'buyer' => $b2bProductReview->user?->buyerName,
                    "rating" => floatval($b2bProductReview->rating),
                    "title" => $b2bProductReview->title,
                    "note" => $b2bProductReview->title,
                ];
            })->toArray() : [],
            'review_count' => (int)$this->b2bProductReview?->count(),
            'b2bLikes' => $this->b2bLikes->count(),
            'images' => $this->b2bProductImages ? $this->b2bProductImages->map(function ($image): array {
                return [
                    'image' => $image->image
                ];
            })->toArray() : [],
            'date' => (string)$this->created_at,
        ];
    }
}
