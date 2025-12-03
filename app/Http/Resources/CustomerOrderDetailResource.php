<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;

class CustomerOrderDetailResource extends JsonResource
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
            'order_no' => (string) $this->resource->order_no,
            'total_amount' => $this->additional['summary']['sub_total'],
            'customer' => (object) [
                'id' => $this->resource->user?->id,
                'name' => trim("{$this->resource->user?->first_name} {$this->resource->user?->last_name}"),
                'email' => $this->resource->user?->email,
                'phone' => $this->resource->user?->phone,
            ],
            'products' => ProductOrderResource::collection($this->resource->products),
            'summary' => $this->resource->additional['summary'] ?? [],
            'shipping_address' => (object) [
                'name' => $this->resource->user?->userShippingAddress()->first()?->first_name.' '.$this->resource->user?->userShippingAddress()->first()?->last_name,
                'phone' => $this->resource->user?->userShippingAddress()->first()?->phone,
                'email' => $this->resource->user?->userShippingAddress()->first()?->email,
                'address' => $this->resource->user?->userShippingAddress()->first()?->street_address,
                'city' => $this->resource->user?->userShippingAddress()->first()?->city,
                'state' => $this->resource->user?->userShippingAddress()->first()?->state,
                'zip' => $this->resource->user?->userShippingAddress()->first()?->zip,
            ],
            'activities' => OrderActivityResource::collection($this->resource->orderActivities),
            'order_date' => Date::parse($this->resource->created_at)->format('d M Y'),
            'order_time' => Date::parse($this->resource->created_at)->format('h:i A'),
            'payment_status' => strtolower($this->resource->payment_status) === 'success' ? 'paid' : 'not-paid',
            'payment_method' => $this->resource->payment_method,
            'expected_delivery' => getExpectedDelivery($this->resource->user?->userCountry),
            'status' => $this->resource->status,
        ];
    }
}
