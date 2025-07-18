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
            'id' => (int) $this->id,
            'country_id' => (int) $this->country_id,
            'name' => (string) $this->name,
            'flag' => (string) $this->flag,
            'currency' => (string) $this->currency,
        ];
    }
}
