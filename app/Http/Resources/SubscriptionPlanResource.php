<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int)$this->id,
            'title' => (string)$this->title,
            'cost' => (int)$this->cost,
            'country_id' => (int)$this->country_id,
            'period' => (string)$this->period,
            'tagline' => (string)$this->tagline,
            'details' => (string)$this->details,
            'status' => (string)$this->status,
        ];
    }
}