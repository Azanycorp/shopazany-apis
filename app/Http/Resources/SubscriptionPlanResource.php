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
            'id' => (int) $this->resource->id,
            'title' => (string) $this->resource->title,
            'cost' => (int) $this->resource->cost,
            'country_id' => (int) $this->resource->country_id,
            'currency' => (string) $this->resource->currency,
            'period' => (string) $this->resource->period,
            'tier' => (int) $this->resource->tier,
            'designation' => (string) $this->resource->designation,
            'tagline' => (string) $this->resource->tagline,
            'details' => (string) $this->resource->details,
            'type' => (string) $this->resource->type,
            'status' => (string) $this->resource->status,
        ];
    }
}
