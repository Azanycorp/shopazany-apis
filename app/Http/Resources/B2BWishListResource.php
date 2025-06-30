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
        $average_rating = $this->b2bProductReview->avg('rating');

        return [
            'id' => (int) $this->id,
            'product' => $this->product,
            'qty' => $this->qty,
            'rating' => floatval($average_rating),
            'review_count' => (int) $this->b2bProductReview?->count(),
        ];
    }
}
