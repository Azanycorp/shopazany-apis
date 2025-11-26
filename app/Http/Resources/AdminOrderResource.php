<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderResource extends JsonResource
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
            'order_no' => (string) $this->resource->order_no,
            'quantity' => (int) $this->resource->products->sum('pivot.product_quantity'),
            'order_date' => (string) $this->resource->order_date,
            'total_amount' => (string) $this->resource->total_amount,
            'payment_method' => (string) $this->resource->payment_method,
            'status' => (string) $this->resource->status,
            'products' => $this->resource->products ? $this->resource->products->map(function ($product): array {
                return [
                    'name' => $product->name,
                    'category' => $product->category?->name,
                    'image' => $product->image,
                    'quantity' => $product->pivot->product_quantity,
                    'price' => $product->pivot->price,
                    'sub_total' => $product->pivot->sub_total,
                ];
            })->toArray() : [],
            'seller' => $this->resource->products->isNotEmpty() ? (object) [
                'name' => "{$this->resource->products->first()->user?->first_name} {$this->resource->products->first()->user?->last_name}",
                'location' => $this->resource->products->first()->user?->address,
            ] : null,
            'customer' => (object) [
                'name' => "{$this->resource->user?->first_name} {$this->resource->user?->last_name}",
                'phone' => $this->resource->user?->phone,
                'email' => $this->resource->user?->email,
            ],
        ];
    }
}
