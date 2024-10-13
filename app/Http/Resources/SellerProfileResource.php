<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SellerProfileResource extends JsonResource
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
            "uuid" => (string)$this->uuid,
            "first_name" => (string)$this->first_name,
            "last_name" => (string)$this->last_name,
            "middlename" => (string)$this->middlename,
            "email" => (string)$this->email,
            "address" => (string)$this->address,
            "city" => (string)$this->city,
            "postal_code" => (string)$this->postal_code,
            "phone" => (string)$this->phone,
            "country_id" => (string)$this->country,
            "state_id" => (string)$this->state_id,
            "referrer_code" => (string)$this->referrer_code,
            "referrer_link" => (string)$this->referrer_link,
            "date_of_birth" => (string)$this->date_of_birth,
            "is_verified" => (boolean)$this->is_verified,
            "income_type" => (string)$this->income_type,
            "image" => (string)$this->image,
            "type" => (string)$this->type,
            "is_affiliate_member" => (boolean)$this->is_affiliate_member,
            'two_factor_enabled' => (boolean)$this->two_factor_enabled,
            "status" => (string)$this->status,
            "business_info" => (object) [
                'business_location' => $this?->businessInformation?->business_location,
                'business_type' => $this?->businessInformation?->business_type,
                'business_name' => $this?->businessInformation?->business_name,
                'business_reg_number' => $this?->businessInformation?->business_reg_number,
                'business_phone' => $this?->businessInformation?->business_phone,
                'country_id' => $this?->businessInformation?->country_id,
                'city' => $this?->businessInformation?->city,
                'address' => $this?->businessInformation?->address,
                'zip' => $this?->businessInformation?->zip,
                'state' => $this?->businessInformation?->state,
                'apartment' => $this?->businessInformation?->apartment,
                'business_reg_document' => $this?->businessInformation?->business_reg_document,
                'identification_type' => $this?->businessInformation?->identification_type,
                'identification_type_document' => $this?->businessInformation?->identification_type_document,
            ],
        ];
    }
}
