<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RfqResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'buyer_id' => $this->buyer_id,
            'seller_id' => $this->seller_id,
            'buyer_unit_price' => $this->buyer_unit_price,
            'seller_unit_price' => $this->seller_unit_price,
            'buyer_total_amount' => $this->buyer_total_amount,
            'seller_total_amount' => $this->seller_total_amount,
            'quote_no' => $this->quote_no,
            'product_id' => $this->product_id,
            'product_quantity' => $this->product_quantity,
            'payment_status' => $this->payment_status,
            'status' => $this->status,
            'type' => $this->type,
            'product_data' => $this->product_data,
            'delivery_date' => $this->delivery_date,
            'shipped_date' => $this->shipped_date,
            'seller' => (object) [
                'first_name' => $this->resource->seller?->first_name,
                'last_name' => $this->resource->seller?->last_name,
                'email' => $this->resource->seller?->email,
                'phone' => $this->resource->seller?->phone,
            ],
            'buyer' => (object) [
                'first_name' => $this->resource->seller?->first_name,
                'last_name' => $this->resource->seller?->last_name,
                'email' => $this->resource->seller?->email,
                'phone' => $this->resource->seller?->phone,
            ],
        ];
    }
}
