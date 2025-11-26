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
            'id' => (int) $this->resource->id,
            'name' => (string) $this->resource->name,
            'location' => (string) $this->resource->location,
            'note' => (string) $this->resource->note,
            'city' => (string) $this->resource->city,
            'country' => $this->resource->country?->name,
            'status' => (string) $this->resource->status,
        ];
    }
}
