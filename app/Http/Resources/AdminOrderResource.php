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
            'id' => (int) $this->id,
            'order_no' => (string) $this->order_no,
            'quantity' => (int) $this->products->sum('pivot.product_quantity'),
            'order_date' => (string) $this->order_date,
            'total_amount' => (string) $this->total_amount,
            'payment_method' => (string) $this->payment_method,
            'status' => (string) $this->status,
            'products' => $this->products ? $this->products->map(function ($product): array {
                return [
                    'name' => $product->name,
                    'category' => $product->category?->name,
                    'image' => $product->image,
                    'quantity' => $product->pivot->product_quantity,
                    'price' => $product->pivot->price,
                    'sub_total' => $product->pivot->sub_total,
                ];
            })->toArray() : [],
            'seller' => $this->products->isNotEmpty() ? (object) [
                'name' => "{$this->products->first()->user?->first_name} {$this->products->first()->user?->last_name}",
                'location' => $this->products->first()->user?->address,
            ] : null,
            'customer' => (object) [
                'name' => "{$this->user?->first_name} {$this->user?->last_name}",
                'phone' => $this->user?->phone,
                'email' => $this->user?->email,
            ],
        ];
    }
}
