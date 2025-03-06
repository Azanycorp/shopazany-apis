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
        $user = $request->user();
        $productCurrency = $this->product?->shopCountry->currency ?? 'USD';
        $userCurrency = $user?->default_currency ?? $productCurrency;

        $amount = currencyConvert(
            $productCurrency,
            $this->total_amount,
            $userCurrency
        );

        return [
            'id' => (int)$this->id,
            'order_no' => (string)$this->order_no,
            'total_amount' => $amount,
            'customer' => (object) [
                'id' => $this->user?->id,
                'name' => $this->user?->first_name . ' ' . $this->user?->last_name,
                'email' => $this->user?->email,
                'phone' => $this->user?->phone,
            ],
            'products' => $this->products ? $this->products->map(function ($product) {
                return [
                    'id' => $product?->id,
                    'name' => $product?->name,
                    'description' => $product?->description,
                    'price' => $product?->price,
                    'quantity' => $this->product_quantity,
                    'sub_total' => $product?->price * $this->product_quantity,
                    'image' => $product?->image,
                ];
            })->toArray() : [],
            'shipping_address' => (object) [
                'name' => $this->user?->userShippingAddress()->first()?->first_name . ' ' . $this->user?->userShippingAddress()->first()?->last_name,
                'phone' => $this->user?->userShippingAddress()->first()?->phone,
                'email' => $this->user?->userShippingAddress()->first()?->email,
                'address' => $this->user?->userShippingAddress()->first()?->street_address,
                'city' => $this->user?->userShippingAddress()->first()?->city,
                'state' => $this->user?->userShippingAddress()->first()?->state,
                'zip' => $this->user?->userShippingAddress()->first()?->zip,
            ],
            'order_date' => Carbon::parse($this->created_at)->format('d M Y'),
            'order_time' => Carbon::parse($this->created_at)->format('h:i A'),
            'payment_status' => strtolower($this->payment_status) === "success" ? "paid" : "not-paid",
            'payment_method' => $this->payment_method,
            'status' => $this->status,
        ];
    }
}
