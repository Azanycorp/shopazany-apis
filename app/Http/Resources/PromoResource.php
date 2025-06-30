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
            'id' => (int) $this->id,
            'coupon_code' => (string) $this->coupon_code,
            'type' => (string) $this->type,
            'start_date' => (string) $this->start_date,
            'end_date' => (string) $this->end_date,
            'discount' => (int) $this->discount,
            'discount_type' => (string) $this->discount_type,
        ];
    }
}
