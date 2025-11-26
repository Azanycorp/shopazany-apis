<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopCountryResource extends JsonResource
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
            'country_id' => (int) $this->resource->country_id,
            'name' => (string) $this->resource->name,
            'flag' => (string) $this->resource->flag,
            'currency' => (string) $this->resource->currency,
        ];
    }
}
