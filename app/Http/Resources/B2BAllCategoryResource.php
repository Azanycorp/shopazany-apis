<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class B2BAllCategoryResource extends JsonResource
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
            'image' => (string) $this->resource->image,
            'subcategory' => $this->resource->subcategory ? $this->resource->subcategory->map(function ($subcategory): array {
                return [
                    'id' => $subcategory?->id,
                    'name' => $subcategory?->name,
                    'image' => $subcategory?->image,
                    'category_id' => $subcategory?->category_id,
                ];
            })->toArray() : [],
        ];
    }
}
