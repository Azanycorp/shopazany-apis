<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class B2BBannerResource extends JsonResource
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
            'title' => (string) $this->resource->title,
            'slug' => (string) $this->resource->slug,
            'image' => (string) $this->resource->image,
            'start_date' => (string) $this->resource->start_date,
            'end_date' => (string) $this->resource->end_date,
            'products' => $this->resource->b2b_products,
            'status' => (string) $this->resource->status,
        ];
    }
}
