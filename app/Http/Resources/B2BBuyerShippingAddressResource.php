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
            'id' => (int) $this->id,
            'address_name' => (string) $this->address_name,
            'name' => (string) $this->name,
            'surname' => (string) $this->surname,
            'email' => (string) $this->email,
            'phone' => (string) $this->phone,
            'street' => (string) $this->street,
            'city' => (string) $this->city,
            'postal_code' => (string) $this->postal_code,
            'state' => (string) $this->state?->name,
            'country' => (string) $this->country?->name,
        ];
    }
}
