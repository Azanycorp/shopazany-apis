<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'uuid' => (string) $this->resource->uuid,
            'first_name' => (string) $this->resource->first_name,
            'last_name' => (string) $this->resource->last_name,
            'middlename' => (string) $this->resource->middlename,
            'phone' => (string) $this->resource->phone,
            'email' => (string) $this->resource->email,
            'date_of_birth' => (string) $this->resource->date_of_birth,
            'image' => (string) $this->resource->image,
            'address' => (object) [
                'address' => (string) $this->resource->address,
                'city' => (string) $this->resource->city,
                'country' => (string) $this->resource->userCountry?->name,
                'state' => (string) $this->resource->state?->name,
            ],
            'is_approved' => $this->resource->is_admin_approve,
            'status' => (string) $this->resource->status,
            'wallet' => (object) [
                'available_balance' => currencyConvertTo($this->resource->wallet?->balance, 'USD'),
                'total_income' => 0,
                'total_withdrawal' => 0,
                'default_currency' => $this->resource->default_currency,
            ],
            'wishlist' => $this->resource->wishlist ? $this->resource->wishlist->map(function ($list): array {
                return [
                    'product_name' => $list->product?->name,
                    'created_at' => $list->product?->created_at,
                ];
            })->toArray() : [],
            'payments' => $this->resource->payments ? $this->resource->payments->map(function ($payment): array {
                return [
                    'id' => $payment->id,
                    'order_no' => $payment?->order?->order_no,
                    'amount' => $payment->amount,
                    'payment_method' => $payment?->order?->payment_method,
                    'status' => $payment->status,
                    'reference' => $payment->reference,
                    'created_at' => $payment->created_at,
                ];
            })->toArray() : [],
        ];
    }
}
