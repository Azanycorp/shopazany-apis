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
            'id' => (int)$this->id,
            'subcription_plan' => (string)$this->subscriptionPlan->title,
            'plan_start' => (string)$this->plan_start,
            'plan_end' => (string)$this->plan_end,
            'expired_at' => (string)$this->expired_at,
            'status' => (string)$this->status,
        ];
    }
}
