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
            'id' => (int) $this->resource->id,
            'uuid' => (string) $this->resource->uuid,
            'first_name' => (string) $this->resource->first_name,
            'last_name' => (string) $this->resource->last_name,
            'middlename' => (string) $this->resource->middlename,
            'email' => (string) $this->resource->email,
            'address' => (string) $this->resource->address,
            'city' => (string) $this->resource->city,
            'postal_code' => (string) $this->resource->postal_code,
            'phone' => (string) $this->resource->phone,
            'country_id' => (string) $this->resource->country,
            'state_id' => (string) $this->resource->state_id,
            'referrer_code' => (string) $this->resource->referrer_code,
            'referrer_link' => (string) $this->resource->referrer_link,
            'date_of_birth' => (string) $this->resource->date_of_birth,
            'is_verified' => (bool) $this->resource->is_verified,
            'income_type' => (string) $this->resource->income_type,
            'image' => (string) $this->resource->image,
            'type' => (string) $this->resource->type,
            'is_affiliate_member' => (bool) $this->resource->is_affiliate_member,
            'two_factor_enabled' => (bool) $this->resource->two_factor_enabled,
            'status' => (string) $this->resource->status,
            'business_info' => (object) [
                'business_location' => $this->resource->businessInformation?->business_location,
                'business_type' => $this->resource->businessInformation?->business_type,
                'business_name' => $this->resource->businessInformation?->business_name,
                'business_reg_number' => $this->resource->businessInformation?->business_reg_number,
                'business_phone' => $this->resource->businessInformation?->business_phone,
                'country_id' => $this->resource->businessInformation?->country_id,
                'city' => $this->resource->businessInformation?->city,
                'address' => $this->resource->businessInformation?->address,
                'zip' => $this->resource->businessInformation?->zip,
                'state' => $this->resource->businessInformation?->state,
                'apartment' => $this->resource->businessInformation?->apartment,
                'business_reg_document' => $this->resource->businessInformation?->business_reg_document,
                'identification_type' => $this->resource->businessInformation?->identification_type,
                'identification_type_document' => $this->resource->businessInformation?->identification_type_document,
            ],
        ];
    }
}
