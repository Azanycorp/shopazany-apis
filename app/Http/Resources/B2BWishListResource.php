<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class B2BWishListResource extends JsonResource
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
            'product' => $this->resource->product,
            'qty' => $this->resource->qty,
            'rating' => floatval($average_rating),
            'review_count' => (int) $this->resource->b2bProductReview?->count(),
        ];
    }
}
