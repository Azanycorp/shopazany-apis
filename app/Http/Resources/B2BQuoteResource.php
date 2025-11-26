<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class B2BQuoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $average_rating = $this->resource->b2bProductReview->isNotEmpty()
            ? $this->resource->b2bProductReview->avg('rating')
            : 0;

        return [
            'id' => (int) $this->resource->id,
            'product' => $this->resource->product,
            'qty' => $this->resource->qty,
            'rating' => floatval($average_rating),
            'review_count' => (int) $this->resource->b2b_product_review_count,
        ];
    }
}
