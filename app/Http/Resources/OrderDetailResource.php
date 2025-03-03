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
            'customer' => (object) [
                'id' => $this->user?->id,
                'name' => $this->user?->first_name . ' ' . $this->user?->last_name,
                'email' => $this->user?->email,
                'phone' => $this->user?->phone,
            ],
            'product' => (object) [
                'id' => $this->product?->id,
                'name' => $this->product?->name,
                'description' => $this->product?->description,
                'price' => $this->product?->price,
                'quantity' => $this->product_quantity,
                'sub_total' => $this->product?->price * $this->product_quantity,
                'image' => $this->product?->image,
            ],
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
