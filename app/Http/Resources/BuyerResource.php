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
            'id' => (int) $this->resource->id,
            'uuid' => (string) $this->resource->uuid,
            'first_name' => (string) $this->resource->first_name,
            'last_name' => (string) $this->resource->last_name,
            'middlename' => (string) $this->resource->middlename,
            'phone' => (string) $this->resource->phone,
            'email' => (string) $this->resource->email,
            'default_currency' => (string) $this->resource->default_currency,
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
            'company' => $this->resource->b2bCompany,
        ];
    }
}
