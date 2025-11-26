<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'street_address' => $this->resource->street_address,
            'state' => $this->resource->state,
            'city' => $this->resource->city,
            'zip' => $this->resource->zip,
        ];
    }
}
