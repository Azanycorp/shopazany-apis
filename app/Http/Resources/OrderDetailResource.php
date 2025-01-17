<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
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
            'total_amount' => (string)$this->total_amount,
            'product' => $this->products ? $this->products->map(function ($product) {
                return [
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'quantity' => $this->product_quantity,
                    'sub_total' => $product->price * $this->product_quantity,
                ];
            })->toArray() : [],
            'billing_address' => (object) [
                'address' => $this->user->address,
                'phone' => $this->user->phone,
                'email' => $this->user->email,
            ],
            'shipping_address' => (object) [
                'address' => $this->user->address,
                'phone' => $this->user->phone,
                'email' => $this->user->email,
            ],
            'order_date' => Carbon::parse($this->created_at)->format('d M Y'),
            'order_time' => Carbon::parse($this->created_at)->format('h:i A'),
        ];
    }
}
