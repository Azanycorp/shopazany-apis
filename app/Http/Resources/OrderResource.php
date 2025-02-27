<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_no' => (string)$this->order_no,
            'customer' => optional($this->user)->first_name . ' ' . optional($this->user)->last_name,
            'quantity' => (string)$this->product_quantity,
            'order_date' => (string)$this->order_date,
            'total_amount' => (string)$this->total_amount,
            'payment_method' => (string)$this->payment_method,
            'currency' => (string)$this->product?->default_currency,
            'status' => (string)$this->status
        ];
    }
}
