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
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'short_description' => $this->resource->short_description,
            'description' => $this->resource->description,
            'image' => $this->resource->image,
            'meta_title' => $this->resource->meta_title,
            'meta_description' => $this->resource->meta_description,
            'meta_keywords' => $this->resource->meta_keywords,
            'meta_image' => $this->resource->meta_image,
            'type' => (string) $this->resource->type,
            'status' => $this->resource->status,
            'created_at' => $this->resource->created_at,
            'blog_category' => $this->whenLoaded('blogCategory', fn () => $this->resource->blogCategory->only('id', 'name', 'slug')),
        ];
    }
}
