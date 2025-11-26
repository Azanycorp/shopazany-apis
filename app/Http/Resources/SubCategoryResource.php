<?php

namespace App\Http\Resources;

use App\Enum\ProductStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubCategoryResource extends JsonResource
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
            'status' => (string) $this->resource->status,
            'subcategory' => (object) [
                'total' => $this->resource->count(),
                'active' => $this->resource->where('status', 'active')->count(),
            ],
            'products' => (object) [
                'active' => $this->resource->products()->where('status', ProductStatus::ACTIVE)->count(),
                'inactive' => $this->resource->products()->where('status', ProductStatus::PENDING)->count(),
            ],
            'category_image' => (string) $this->resource->category?->image,
        ];
    }
}
