<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class B2BOrderResource extends JsonResource
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
            'buyer' => (string)$this->buyer?->first_name,
            'seller' => (string)$this->seller?->first_name,
            'product_quantity' => (string)$this->product_quantity,
            'order_no' => (string)$this->order_no,
            'shipping_address' => (string)$this->shipping_address,
            'billing_address' => (string)$this->billing_address,
            'product_data' => (string)$this->product_data,
            'total_amount' => (string)$this->total_amount,
            'payment_method' => (string)$this->payment_method,
            'payment_status' => (string)$this->payment_status,
            'status' => (string)$this->status,
            'delivery_date' => (string)$this->delivery_date,
            'shipped_date' => (string)$this->delivery_date,

        ];
    }
}
