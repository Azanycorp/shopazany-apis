<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'product_id' => $this->resource->product_id,
            'rating' => $this->resource->rating,
            'review' => $this->resource->review,
            'created_at' => $this->resource->created_at,
            'user' => (object) [
                'id' => $this->resource->user?->id,
                'first_name' => $this->resource->user?->first_name,
                'last_name' => $this->resource->user?->last_name,
            ],
        ];
    }
}
