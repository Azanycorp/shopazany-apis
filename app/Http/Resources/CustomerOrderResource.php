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

        $totalAmountForSeller = $this->products->sum(function ($product) use ($user): float {
            return currencyConvert(
                $product->shopCountry->currency ?? 'USD',
                $product->pivot->sub_total,
                $user->default_currency
            );
        });

        return [
            'id' => (int) $this->id,
            'order_no' => (string) $this->order_no,
            'customer' => optional($this->user)->first_name.' '.optional($this->user)->last_name,
            'order_date' => (string) $this->order_date,
            'total_amount' => $totalAmountForSeller,
            'payment_method' => (string) $this->payment_method,
            'products' => ProductOrderResource::collection($this->products),
            'expected_delivery' => getExpectedDelivery($this->user?->userCountry),
            'status' => (string) $this->status,
        ];
    }
}
