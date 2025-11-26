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
            'id' => (int) $this->resource->id,
            'collationCentre' => $this->resource->collationCentre?->name,
            'shipment_id' => $this->resource->shippment_id,
            'order_number' => $this->resource->order_number,
            'hub' => $this->resource->hub?->name,
            'package' => $this->resource->package,
            'customer' => (object) $this->resource->customer,
            'vendor' => (object) $this->resource->vendor,
            'status' => $this->resource->status,
            'priority' => $this->resource->priority,
            'expected_delivery_date' => $this->resource->expected_delivery_date,
            'start_origin' => $this->resource->start_origin,
            'current_location' => $this->resource->current_location,
            'items' => $this->resource->items,
            'logged_items' => $this->resource->logged_items,
            'dispatch_name' => $this->resource->dispatch_name,
            'destination_name' => $this->resource->destination_name,
            'dispatch_phone' => $this->resource->dispatch_phone,
            'expected_delivery_time' => $this->resource->expected_delivery_time,
            'reciever_name' => $this->resource->reciever_name,
            'reciever_phone' => $this->resource->reciever_phone,
            'vehicle_number' => $this->resource->vehicle_number,
            'delivery_address' => $this->resource->delivery_address,
            'item_condition' => $this->resource->item_condition,
            'transfer_reason' => $this->resource->transfer_reason,
            'created_at' => $this->resource->created_at,
        ];
    }
}
