<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
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
            'code' => (string) $this->resource->sortname,
            'name' => (string) $this->resource->name,
            'phonecode' => (string) $this->resource->phonecode,
        ];
    }
}
