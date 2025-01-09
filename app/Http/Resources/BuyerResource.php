<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuyerResource extends JsonResource
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
            'uuid' => (string)$this->uuid,
            "first_name" => (string)$this->first_name,
            "last_name" => (string)$this->last_name,
            "middlename" => (string)$this->middlename,
            "phone" => (string)$this->phone,
            "email" => (string)$this->email,
            "date_of_birth" => (string)$this->date_of_birth,
            "image" => (string)$this->image,
            "address" => (object)[
                "address" => (string)$this->address,
                "city" => (string)$this->city,
                "country" => (string)optional($this->userCountry)->name,
                "state" => (string)optional($this->state)->name,
            ],
            "is_approved" => $this->is_admin_approve,
            "status" => (string)$this->status,
            "wallet" => (object)[
                'available_balance' => optional($this->wallet)->balance,
                'total_income' => 0,
                'total_withdrawal' => 0
            ],
            'wishlist' => $this->wishlist ? $this->wishlist->map(function ($list) {
                return [
                    'product_name' => $list->product?->name,
                    'created_at' => $list->product?->created_at,
                ];
            })->toArray() : [],
            'payments' => $this->payments ? $this->payments->map(function ($payment) {
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
