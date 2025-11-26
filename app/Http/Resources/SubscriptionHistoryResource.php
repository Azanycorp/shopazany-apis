<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionHistoryResource extends JsonResource
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
            'subcription_plan' => (string) $this->resource->subscriptionPlan->title,
            'plan_start' => (string) $this->resource->plan_start,
            'plan_end' => (string) $this->resource->plan_end,
            'expired_at' => (string) $this->resource->expired_at,
            'status' => (string) $this->resource->status,
        ];
    }
}
