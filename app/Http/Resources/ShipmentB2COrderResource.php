<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentB2COrderResource extends JsonResource
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
            'order_date' => (string) $this->resource->order_date,
            'total_amount' => $this->resource->total_amount,
            'payment_method' => (string) $this->resource->payment_method,
            'status' => (string) $this->resource->status,
            'customer' => $this->whenLoaded('user', fn ($user) => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'address' => $user->address,
            ]),
            'products' => $this->whenLoaded('products', fn ($products) => $products->map(fn ($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => $product->pivot->product_quantity,
                'price' => $product->pivot->price,
                'sub_total' => $product->pivot->sub_total,
                'image' => $product->image,
            ])),
            'vendor' => $this->whenLoaded('products', fn () => [
                'id' => $this->resource->products->first()?->user->id,
                'business_name' => $this->resource->products->first()?->user->company_name,
                'contact' => $this->resource->products->first()?->user->phone,
                'location' => $this->resource->products->first()?->user->address,
            ]),
        ];
    }
}
