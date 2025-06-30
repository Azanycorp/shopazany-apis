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
            'id' => (int) $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'slug' => $this->slug,
            'author' => $this->user?->first_name,
            'image' => $this->image,
            'description' => $this->description,
            'date' => $this->created_at->toDateString(),
        ];
    }
}
