<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
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
            'image' => (string) $this->resource->image,
            'deal' => $this->resource->deal,
            'start_date' => (string) $this->resource->start_date,
            'end_date' => (string) $this->resource->end_date,
            'products' => $this->resource->products,
            'type' => (string) $this->resource->type,
            'status' => (string) $this->resource->status,
        ];
    }
}
