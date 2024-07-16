<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => (int)$this->id,
            "uuid" => (int)$this->uuid,
            "first_name" => (string)$this->first_name,
            "last_name" => (string)$this->last_name,
            "email" => (string)$this->email,
            "address" => (string)$this->address,
            "city" => (string)$this->city,
            "postal_code" => (string)$this->postal_code,
            "phone" => (string)$this->phone,
            "country" => (string)$this->country,
            "referrer_code" => (string)$this->referrer_code,
            "referrer_link" => (string)$this->referrer_link,
            "date_of_birth" => (string)$this->date_of_birth,
            "is_verified" => (int)$this->is_verified,
            "income_type" => (string)$this->income_type,
            "image" => (string)$this->image,
            "is_affiliate_member" => (int)$this->is_affiliate_member === 1 ? true : false,
            "status" => (string)$this->status,
            "wallet" => (object)[
                'available_balance' => optional($this->wallet)->balance,
                'total_income' => 0,
                'total_withdrawal' => 0
            ],
            "no_of_referrals" => $this->referrals->count(),
            "bank_account" => (object)[
                'account_name' => optional($this->bankAccount)->account_name,
                'bank_name' => optional($this->bankAccount)->bank_name,
                'account_number' => optional($this->bankAccount)->account_number,
            ],
            "business_info" => (object) [
                'business_location' => optional($this->userbusinessinfo)->business_location,
                'business_type' => optional($this->userbusinessinfo)->business_type,
                'identity_type' => optional($this->userbusinessinfo)->identity_type,
                'file' => optional($this->userbusinessinfo)->file,
                'status' => optional($this->userbusinessinfo)->status
            ]
        ];
    }
}
