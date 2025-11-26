<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminNotificationResource extends JsonResource
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
            'title' => (string) $this->resource->title,
            'content' => (string) $this->resource->content,
            'is_read' => (bool) $this->resource->is_read,
            'created_at' => $this->resource->created_at->toDateString(),
        ];
    }
}
