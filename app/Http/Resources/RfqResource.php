<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RfqResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $sourceCurrency = $this->product->shopCountry->currency ?? 'USD';
        $targetCurrency = auth()->user()->default_currency;

        return [
            'id' => $this->id,
            'buyer_id' => $this->buyer_id,
            'seller_id' => $this->seller_id,
            'buyer_unit_price' => $this->buyer_unit_price,
            'seller_unit_price' => $this->seller_unit_price,
            'total_amount' => currencyConvert(
                $sourceCurrency,
                $this->total_amount,
                $targetCurrency
            ),
            'seller' => (object) [
                'first_name' => $this->resource->seller?->first_name,
                'last_name' => $this->resource->seller?->last_name,
                'email' => $this->resource->seller?->email,
                'phone' => $this->resource->seller?->phone,
            ],
            'buyer' => (object) [
                'first_name' => $this->resource->seller?->first_name,
                'last_name' => $this->resource->seller?->last_name,
                'email' => $this->resource->seller?->email,
                'phone' => $this->resource->seller?->phone,
            ],
        ];
    }
}
