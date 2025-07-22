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
            'id' => (int) $this->id,
            'collationCentre' => $this->collationCentre?->name,
            'batch_id' => $this->batch_id,
            'shippment_ids' => $this->current_location,
            'items' => $this->items,
            'status' => $this->status,
            'priority' => $this->priority,
            'destination_state' => $this->destination_state,
            'destination_centre' => $this->destination_centre,
            'vehicle' => $this->vehicle,
            'driver_name' => $this->driver_name,
            'departure' => $this->departure,
            'arrival' => $this->arrival,
            'note' => $this->note,
            'date' => $this->created_at->todateString(),
        ];
    }
}
