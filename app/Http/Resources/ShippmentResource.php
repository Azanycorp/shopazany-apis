<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippmentResource extends JsonResource
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
            'shippment_id' => $this->shippment_id,
            'package' => (object)$this->package,
            'customer' => (object)$this->customer,
            'vendor' => (object)$this->vendor,
            'status' => $this->status,
            'priority' => $this->priority,
            'expected_delivery_date' => $this->expected_delivery_date,
            'start_origin' => $this->start_origin,
            'current_location' => $this->current_location,
            'activity' => $this->activity,
            'note' => $this->note,
            'items' => $this->items,
            'dispatch_name' => $this->dispatch_name,
            'destination_name' => $this->destination_name,
            'dispatch_phone' => $this->dispatch_phone,
            'expected_delivery_time' => $this->expected_delivery_time,
            'activities' => $this->activities ? $this->activities->map(function ($activity): array {
                return [
                    'action' => $activity?->action,
                    'date' => $activity?->created_at->todateString(),

                ];
            })->toArray() : [],
        ];
    }
}
