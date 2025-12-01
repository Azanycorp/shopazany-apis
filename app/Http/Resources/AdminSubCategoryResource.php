<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminSubCategoryResource extends JsonResource
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
            'product_count' => (int) $this->resource->products_count,
            'category_count' => (int) $this->resource->category_count,
            'type' => (string) $this->resource->type,
            'status' => (string) $this->resource->status,
        ];
    }
}
