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
        $sourceCurrency = $this->product->default_currency ?? 'USD';
        $targetCurrency = auth()->user()->default_currency;

        return [
            'id' => (int) $this->resource->id,
            'buyer_id' => $this->resource->buyer_id,
            'seller_id' => $this->resource->seller_id,
            'buyer_unit_price' => currencyConvert(
                $sourceCurrency,
                $this->resource->buyer_unit_price,
                $targetCurrency
            ),
            'seller_unit_price' => currencyConvert(
                $sourceCurrency,
                $this->resource->seller_unit_price,
                $targetCurrency
            ),
            'buyer_total_amount' => currencyConvert(
                $sourceCurrency,
                $this->resource->buyer_total_amount,
                $targetCurrency
            ),
            'seller_total_amount' => currencyConvert(
                $sourceCurrency,
                $this->resource->seller_total_amount,
                $targetCurrency
            ),
            'quote_no' => $this->resource->quote_no,
            'product_id' => $this->resource->product_id,
            'product_quantity' => $this->resource->product_quantity,
            'payment_status' => $this->resource->payment_status,
            'status' => $this->resource->status,
            'type' => $this->resource->type,
            'product_data' => $this->resource->product_data,
            'delivery_date' => $this->resource->delivery_date,
            'shipped_date' => $this->resource->shipped_date,
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
