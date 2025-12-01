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
            'id' => (int) $this->resource->id,
            'uuid' => (string) $this->resource->uuid,
            'first_name' => (string) $this->resource->first_name,
            'last_name' => (string) $this->resource->last_name,
            'middlename' => (string) $this->resource->middlename,
            'phone' => (string) $this->resource->phone,
            'email' => (string) $this->resource->email,
            'image' => (string) $this->resource->image,
            'address' => (string) $this->resource->address,
            'city' => (string) $this->resource->city,
            'country_id' => (string) $this->resource->country,
            'state_id' => (string) $this->resource->state_id,
            'product_count' => $this->resource->b2bProducts->count(),
            'is_approved' => $this->resource->is_admin_approve,
            'status' => (string) $this->resource->status,
            'products' => (object) [
                'account_name' => $this->resource->bankAccount?->account_name,
                'bank_name' => $this->resource->bankAccount?->bank_name,
                'account_number' => $this->resource->bankAccount?->account_number,
            ],
            'bank_account' => (object) [
                'account_name' => $this->resource->bankAccount?->account_name,
                'bank_name' => $this->resource->bankAccount?->bank_name,
                'account_number' => $this->resource->bankAccount?->account_number,
            ],
            'wallet' => (object) [
                'available_balance' => $this->resource->wallet?->balance,
                'total_income' => 0,
                'total_withdrawal' => 0,
            ],
        ];
    }
}
