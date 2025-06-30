<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerResource extends JsonResource
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
            'uuid' => (string) $this->uuid,
            'first_name' => (string) $this->first_name,
            'last_name' => (string) $this->last_name,
            'middlename' => (string) $this->middlename,
            'phone' => (string) $this->phone,
            'email' => (string) $this->email,
            'image' => (string) $this->image,
            'address' => (string) $this->address,
            'city' => (string) $this->city,
            'country_id' => (string) $this->country,
            'state_id' => (string) $this->state_id,
            'product_count' => $this->b2bProducts->count(),
            'is_approved' => $this->is_admin_approve,
            'status' => (string) $this->status,
            'products' => (object) [
                'account_name' => optional($this->bankAccount)->account_name,
                'bank_name' => optional($this->bankAccount)->bank_name,
                'account_number' => optional($this->bankAccount)->account_number,
            ],
            'bank_account' => (object) [
                'account_name' => optional($this->bankAccount)->account_name,
                'bank_name' => optional($this->bankAccount)->bank_name,
                'account_number' => optional($this->bankAccount)->account_number,
            ],
            'wallet' => (object) [
                'available_balance' => optional($this->wallet)->balance,
                'total_income' => 0,
                'total_withdrawal' => 0,
            ],

        ];
    }
}
