<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        $productCurrency = $this->product?->shopCountry->currency ?? 'USD';

        $userCurrency = $user?->default_currency ?? $productCurrency;

        $amount = currencyConvert(
            $productCurrency,
            $this->total_amount,
            $userCurrency
        );

        return [
            'id' => (int)$this->id,
            'order_no' => (string)$this->order_no,
            'customer' => optional($this->user)->first_name . ' ' . optional($this->user)->last_name,
            'quantity' => (string)$this->product_quantity,
            'order_date' => (string)$this->order_date,
            'total_amount' => $amount,
            'payment_method' => (string)$this->payment_method,
            'currency' => (string)$this->product?->shopCountry->currency,
            'status' => (string)$this->status
        ];
    }
}
