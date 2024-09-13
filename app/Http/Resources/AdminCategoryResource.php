<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminCategoryResource extends JsonResource
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
            'is_featured' => $this->featured,
            'product_count' => (int)$this->product_count,
            'sub_category_count' => (int)$this->subcategory_count,
            'status' => (string)$this->status,
        ];
    }
}
