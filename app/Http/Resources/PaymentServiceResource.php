<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentServiceResource extends JsonResource
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
            'slug' => (string) $this->resource->slug,
            'countries' => $this->resource->countries ? $this->resource->countries->map(function ($country): array {
                return [
                    'id' => $country->id,
                    'name' => $country->name,
                ];
            })->toArray() : [],
        ];
    }
}
