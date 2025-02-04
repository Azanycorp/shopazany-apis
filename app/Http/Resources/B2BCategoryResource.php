<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class B2BCategoryResource extends JsonResource
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
            'image' => (string)$this->image,
            'products' => (string)$this->products
        ];
    }
}
