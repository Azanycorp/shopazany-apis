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
            'id' => (int) $this->resource->id,
            'product_quantity' => (string) $this->resource->product_quantity,
            'order_no' => (string) $this->resource->order_no,
            'shipping_address' => $this->resource->shipping_address,
            'shipping_agent' => $this->resource->shipping_agent,
            'collation_center' => $this->resource->collationCentre?->name,
            'billing_address' => $this->resource->billing_address,
            'product_data' => $this->resource->product_data,
            'total_amount' => (string) $this->resource->total_amount,
            'payment_method' => (string) $this->resource->payment_method,
            'payment_status' => (string) $this->resource->payment_status,
            'status' => (string) $this->resource->status,
            'country' => (string) $this->resource->country?->name,
            'delivery_date' => (string) $this->resource->delivery_date,
            'shipped_date' => (string) $this->resource->shipped_date,
            'seller' => (object) [
                'first_name' => $this->resource->seller?->first_name,
                'last_name' => $this->resource->seller?->last_name,
                'email' => $this->resource->seller?->email,
                'phone' => $this->resource->seller?->phone,
            ],
            'buyer' => (object) [
                'first_name' => $this->resource->buyer?->first_name,
                'last_name' => $this->resource->buyer?->last_name,
                'email' => $this->resource->buyer?->email,
                'phone' => $this->resource->buyer?->phone,
            ],
        ];
    }
}
