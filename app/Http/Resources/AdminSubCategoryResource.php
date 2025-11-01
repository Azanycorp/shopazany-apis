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
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'slug' => (string) $this->slug,
            'image' => (string) $this->image,
            'product_count' => (int) $this->product_count,
            'category_count' => (int) $this->category_count,
            'type' => (string) $this->type,
            'status' => (string) $this->status,
        ];
    }
}
