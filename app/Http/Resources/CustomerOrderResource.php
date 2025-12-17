<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        $totalAmountForSeller = $this->resource->products->sum(function ($product) use ($user): float {
            return currencyConvert(
                $product->shopCountry->currency ?? 'USD',
                $product->pivot->sub_total,
                $user?->default_currency ?? 'USD'
            );
        });

        return [
            'id' => (int) $this->resource->id,
            'order_no' => (string) $this->resource->order_no,
            'customer' => "{$this->resource->user?->first_name} {$this->resource->user?->last_name}",
            'order_date' => (string) $this->resource->order_date,
            'total_amount' => $totalAmountForSeller,
            'payment_method' => (string) $this->resource->payment_method,
            'products' => ProductOrderResource::collection($this->resource->products),
            'expected_delivery' => getExpectedDelivery($this->resource->user?->userCountry),
            'status' => (string) $this->resource->status,
        ];
    }
}
