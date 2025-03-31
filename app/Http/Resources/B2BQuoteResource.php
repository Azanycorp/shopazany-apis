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
        $average_rating = $this->b2bProductReview->isNotEmpty()
            ? $this->b2bProductReview->avg('rating')
            : 0;

        return [
            'id' => (int)$this->id,
            'product' => $this->product,
            'qty' => $this->qty,
            'rating' => floatval($average_rating),
            'review_count' => (int)$this->b2b_product_review_count,
        ];
    }
}
