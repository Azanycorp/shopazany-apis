<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class B2BBuyerShippingAddressResource extends JsonResource
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
            'address_name' => (string) $this->resource->address_name,
            'name' => (string) $this->resource->name,
            'surname' => (string) $this->resource->surname,
            'email' => (string) $this->resource->email,
            'phone' => (string) $this->resource->phone,
            'street' => (string) $this->resource->street,
            'city' => (string) $this->resource->city,
            'postal_code' => (string) $this->resource->postal_code,
            'state' => (string) $this->resource->state?->resource->name,
            'country' => (string) $this->resource->country?->resource->name,
        ];
    }
}
