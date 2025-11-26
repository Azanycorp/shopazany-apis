<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
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
            'title' => $this->resource->title,
            'type' => $this->resource->type,
            'slug' => $this->resource->slug,
            'author' => $this->resource->user?->first_name,
            'image' => $this->resource->image,
            'description' => $this->resource->description,
            'date' => $this->resource->created_at->toDateString(),
        ];
    }
}
