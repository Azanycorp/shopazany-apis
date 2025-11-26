<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'order_no' => $this->resource->order?->order_no,
            'user' => (object) [
                'first_name' => $this->resource->user?->first_name,
                'last_name' => $this->resource->user?->last_name,
                'middlename' => $this->resource->user?->middlename,
                'email' => $this->resource->user?->email,
                'phone' => $this->resource->user?->phone,
                'store_name' => "{$this->resource->user?->first_name} {$this->resource->user?->last_name}",
            ],
            'amount' => $this->resource->amount,
            'payment_method' => $this->resource->order?->payment_method,
            'status' => $this->resource->status,
            'reference' => $this->resource->reference,
            'created_at' => $this->resource->created_at,
        ];
    }
}
