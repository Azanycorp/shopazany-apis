<?php

namespace App\Http\Resources;

use App\Models\Shipment;
use App\Models\Shippment;
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
            'id' => (int) $this->id,
            'collationCentre' => $this->collationCentre?->name,
            'batch_id' => $this->batch_id,
            'shipments' => $this->shippments,
            'items' => $this->items,
            'status' => $this->status,
            'priority' => $this->priority,
            'destination_state' => $this->destination_state,
            'destination_centre' => $this->destination_centre,
            'vehicle' => $this->vehicle,
            'driver_name' => $this->driver_name,
            'driver_phone' => $this->driver_phone,
            'departure' => $this->departure,
            'arrival' => $this->arrival,
            'note' => $this->note,
            'date' => $this->created_at->todateString(),
            'activities' => $this->activities ? $this->activities->map(function ($activity): array {
                return [
                    'action' => $activity?->comment,
                    'note' => $activity?->note,
                    'date' => $activity?->created_at->todateString(),

                ];
            })->toArray() : [],
        ];
    }
}
