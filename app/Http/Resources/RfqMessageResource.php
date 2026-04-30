<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RfqMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $sourceCurrency = $this->resource->seller->default_currency ?? 'USD';
        $targetCurrency = auth()->user()->default_currency;

        return [
            'id' => (int) $this->resource->id,
            'buyer_id' => $this->resource->buyer_id,
            'seller_id' => $this->resource->seller_id,
            'seller_unit_price' => $this->resource->seller_unit_price,
            'p_unit_price' => currencyConvert(
                $sourceCurrency,
                $this->resource->p_unit_price,
                $targetCurrency
            ),
            'seller' => (object) [
                'first_name' => $this->resource->seller?->first_name,
                'last_name' => $this->resource->seller?->last_name,
                'email' => $this->resource->seller?->email,
                'phone' => $this->resource->seller?->phone,
            ],
            'buyer' => (object) [
                'first_name' => $this->resource->buyer?->first_name,
                'last_name' => $this->resource->buyer?->last_name,
                'email' => $this->resource->buyer?->email,
                'phone' => $this->resource->buyer?->phone,
            ],
            'note' => $this->resource->note,
        ];
    }
}
