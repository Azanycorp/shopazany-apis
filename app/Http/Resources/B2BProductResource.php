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
        return [
            'id' => (int)$this->id,
            'name' => (string)$this->name,
            'slug' => (string)$this->slug,
            'category' => (string)$this->category?->name,
            'subCategory' => (string)$this->subCategory?->name,
            'price' => (string)$this->unit_price,
            'description' => (string)$this->description,
            'vendor' => (string)$this->user?->first_name,
            'quantity' => (string)$this->quantity,
            'availability_quantity' => (string)$this->availability_quantity,
            'keywords' => $this?->keywords,
            'moq' => (string)$this->minimum_order_quantity,
            'status' => (string)$this->status,
            'country' => (string)$this->country?->name,
            'review_count' => (int)$this->b2bProductReview?->count(),
            'b2bLikes' => $this->b2bLikes->count(),
            'images' => (string)$this->b2bProductImages !== '' && (string)$this->b2bProductImages !== '0' ? $this->b2bProductImages->map(function ($image): array {
                return [
                    'image' => $image->image
                ];
            })->toArray() : [],
            'date' => (string)$this->created_at,
        ];
    }
}
