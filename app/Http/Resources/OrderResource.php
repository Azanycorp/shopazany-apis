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
        //$amount = currencyConvert($this->product?->shopCountry->currency, $this->total_amount, $this->user?->default_currency);
        return [
            'id' => (int)$this->id,
            'order_no' => (string)$this->order_no,
            'customer' => optional($this->user)->first_name . ' ' . optional($this->user)->last_name,
            'quantity' => (string)$this->product_quantity,
            'order_date' => (string)$this->order_date,
            'total_amount' => $this->total_amount,
            'payment_method' => (string)$this->payment_method,
            'currency' => (string)$this->product?->shopCountry->currency,
            'status' => (string)$this->status
        ];
    }
}
