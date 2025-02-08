<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\B2BProductResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingCountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int)$this->id,
            'name' => (string)$this->name,
            'code' => (string)$this->code,
            'zone' => (string)$this->zone,
            'status' => (string)$this->status,
        ];
    }
}
