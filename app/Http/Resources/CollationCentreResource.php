<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollationCentreResource extends JsonResource
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
            'location' => (string)$this->location,
            'city' => (string)$this->city,
            'country_id' => $this->country?->name,
            'status' => (string)$this->status,
        ];
    }
}
