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
            'id' => (int) $this->id,
            'order_no' => (string) $this->order_no,
            'order_date' => (string) $this->order_date,
            'total_amount' => $this->total_amount,
            'payment_method' => (string) $this->payment_method,
            'status' => (string) $this->status,
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
                'id' => optional($this->products->first())->user->id,
                'business_name' => optional($this->products->first())->user->company_name,
                'contact' => optional($this->products->first())->user->phone,
                'location' => optional($this->products->first())->user->address,
            ]),
        ];
    }
}
