<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'product_quantity' => (string) $this->product_quantity,
            'product_data' => $this->product_data,
            'total_amount' => (string) $this->total_amount,
            'vendor' => (object) [
                'business_name' => $this?->seller?->businessInformation?->business_name,
                'contact' => $this?->seller?->businessInformation?->business_phone,
                'location' => $this?->seller?->businessInformation?->business_location,
            ],
            'customer' => (object) [
                'name' => $this?->buyer?->fullName,
                'email' => $this?->buyer?->email,
                'phone' => $this?->buyer?->phone,
                'city' => $this?->buyer?->city,
                'address' => $this?->buyer->address,
            ],
        ];
    }
}
