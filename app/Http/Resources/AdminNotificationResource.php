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
            'id' => $this->id,
            'title' => (string)$this->title,
            'content' => (string)$this->content,
            'is_read' => (bool) $this->is_read,
            'created_at' => $this->created_at->toDateString()
        ];
    }
}
