<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class B2BBestSellingProductResource extends JsonResource
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
            'id' => $this->resource->product_id,
            'product' => $this->resource->product,
            'total_sold' => $this->resource->total_sold,
            'rating' => floatval($average_rating),
            'review_count' => (int) $this->resource->b2bProductReview?->count(),
        ];
    }
}
