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
                'balance' => optional($this->wallet)->balance
            ],
            "no_of_referrals" => $this->referrals->count()
        ];
    }
}
