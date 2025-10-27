<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class B2CBlogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'image' => $this->image,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'meta_image' => $this->meta_image,
            'type' => (string) $this->type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'blog_category' => $this->whenLoaded('blogCategory', fn () => $this->blogCategory->only('id', 'name', 'slug')),
        ];
    }
}
