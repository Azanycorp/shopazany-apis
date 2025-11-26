<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->resource->id,
            'coupon_code' => (string) $this->resource->coupon_code,
            'type' => (string) $this->resource->type,
            'start_date' => (string) $this->resource->start_date,
            'end_date' => (string) $this->resource->end_date,
            'discount' => (int) $this->resource->discount,
            'discount_type' => (string) $this->resource->discount_type,
        ];
    }
}
