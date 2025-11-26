<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class SearchB2BOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_quantity' => (string) $this->resource->product_quantity,
            'product' => (new Collection($this->resource->product_data))->only(['name', 'fob_price', 'front_image']),
            'total_amount' => (string) $this->resource->total_amount,
            'vendor' => (object) [
                'business_name' => $this->resource->seller?->businessInformation?->business_name,
                'contact' => $this->resource->seller?->businessInformation?->business_phone,
                'location' => $this->resource->seller?->businessInformation?->business_location,
            ],
            'customer' => (object) [
                'name' => $this->resource->buyer?->fullName,
                'email' => $this->resource->buyer?->email,
                'phone' => $this->resource->buyer?->phone,
                'city' => $this->resource->buyer?->city,
                'address' => $this->resource->buyer->address,
            ],
        ];
    }
}
