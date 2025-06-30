<?php

namespace App\Http\Resources;

use App\Enum\ProductStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class B2BSubCategoryResource extends JsonResource
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
            'name' => (string) $this->name,
            'slug' => (string) $this->slug,
            'image' => (string) $this->image,
            'status' => (string) $this->status,
            'subcategory' => (object) [
                'total' => $this->count(),
                'active' => $this->where('status', 'active')->count(),
            ],
            'products' => (object) [
                'active' => $this->products()->where('status', ProductStatus::ACTIVE)->count(),
                'inactive' => $this->products()->where('status', ProductStatus::PENDING)->count(),
            ],
            'category_image' => (string) $this->category?->image,
        ];
    }
}
