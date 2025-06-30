<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => (string) $this->email,
            'business_name' => (string) $this->company_name,
            'address' => (string) $this->address,
            'city' => (string) $this->city,
            'postal_code' => (string) $this->postal_code,
            'phone' => (string) $this->phone,
            'country_id' => (string) $this->country,
            'state_id' => (string) $this->state_id,
            'default_currency' => (string) $this->default_currency,
            'referrer_code' => (string) $this->referrer_code,
            'referrer_link' => $this->referrer_link,
            'date_of_birth' => (string) $this->date_of_birth,
            'is_verified' => (bool) $this->is_verified,
            'income_type' => (string) $this->income_type,
            'image' => (string) $this->image,
            'type' => (string) $this->type,
            'is_affiliate_member' => (bool) $this->is_affiliate_member,
            'two_factor_enabled' => (bool) $this->two_factor_enabled,
            'status' => (string) $this->status,
        ];
    }
}
