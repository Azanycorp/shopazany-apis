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
            'id' => (int)$this->id,
            'name' => (string)$this->name,
            'slug' => (string)$this->slug,
            'countries' => $this->countries ? $this->countries->map(function ($country): array {
                return [
                    'id' => $country->id,
                    'name' => $country->name
                ];
            })->toArray() : []
        ];
    }
}
