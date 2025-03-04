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
            'product_quantity' => (string)$this->product_quantity,
            'order_no' => (string)$this->order_no,
            'shipping_address' => $this->shipping_address,
            'shipping_agent' => $this->shipping_agent,
            'collation_center' => $this->collationCentre?->name,
            'billing_address' => $this->billing_address,
            'product_data' => $this->product_data,
            'total_amount' => (string)$this->total_amount,
            'payment_method' => (string)$this->payment_method,
            'payment_status' => (string)$this->payment_status,
            'status' => (string)$this->status,
            'country' => (string)$this->country?->name,
            'delivery_date' => (string)$this->delivery_date,
            'shipped_date' => (string)$this->shipped_date,
            "seller" => (object) [
                'first_name' => $this?->seller?->first_name,
                'last_name' => $this?->seller?->last_name,
                'email' => $this?->seller?->email,
                'phone' => $this?->seller?->phone,
            ],
            "buyer" => (object) [
                'first_name' => $this?->buyer?->first_name,
                'last_name' => $this?->buyer?->last_name,
                'email' => $this?->buyer?->email,
                'phone' => $this?->buyer?->phone,
            ],
        ];
    }
}
