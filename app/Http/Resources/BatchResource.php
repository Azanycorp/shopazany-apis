<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BatchResource extends JsonResource
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
            'collationCentre' => $this->resource->collationCentre?->name,
            'batch_id' => $this->resource->batch_id,
            'shipments' => $this->resource->shippments,
            'items' => $this->resource->items,
            'status' => $this->resource->status,
            'priority' => $this->resource->priority,
            'destination_state' => $this->resource->destination_state,
            'destination_centre' => $this->resource->destination_centre,
            'vehicle' => $this->resource->vehicle,
            'driver_name' => $this->resource->driver_name,
            'driver_phone' => $this->resource->driver_phone,
            'departure' => $this->resource->departure,
            'arrival' => $this->resource->arrival,
            'note' => $this->resource->note,
            'date' => $this->resource->created_at->todateString(),
            'activities' => $this->resource->activities ? $this->resource->activities->map(function ($activity): array {
                return [
                    'action' => $activity?->comment,
                    'note' => $activity?->note,
                    'date' => $activity?->created_at->todateString(),

                ];
            })->toArray() : [],
        ];
    }
}
