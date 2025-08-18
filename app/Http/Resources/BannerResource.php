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
            'id' => (int) $this->id,
            'title' => (string) $this->title,
            'image' => (string) $this->image,
            'deal' => $this->deal,
            'start_date' => (string) $this->start_date,
            'end_date' => (string) $this->end_date,
            'products' => $this->products,
            'status' => (string) $this->status,
        ];
    }
}
