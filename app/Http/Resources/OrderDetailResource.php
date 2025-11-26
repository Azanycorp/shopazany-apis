<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;

class OrderDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $sellerProducts = $this->resource->products->filter(function ($product) use ($user): bool {
            return $product->user_id === $user->id;
        });
        $userCurrency = $user->default_currency ?? 'USD';
        $totalConvertedAmount = 0;

        $products = $sellerProducts->map(function ($product) use ($userCurrency, &$totalConvertedAmount): array {
            $productCurrency = $product->shopCountry->currency ?? 'USD';

            $convertedSubTotal = currencyConvert(
                $productCurrency,
                $product->pivot->sub_total,
                $userCurrency
            );

            $totalConvertedAmount += $convertedSubTotal;
            $selectedVariation = $product->productVariations->firstWhere('id', $product->pivot->variation_id);

            return [
                'id' => (int) $product->id,
                'name' => (string) $product->name,
                'description' => (string) $product->description,
                'price' => (float) $product->pivot->price,
                'quantity' => (int) $product->pivot->product_quantity,
                'sub_total' => $convertedSubTotal,
                'original_currency' => (string) $productCurrency,
                'image' => (string) $product->image,
                'status' => (string) $product->pivot->status,
                'variation' => $selectedVariation ? [
                    'id' => $selectedVariation->id,
                    'variation' => $selectedVariation->variation,
                    'sku' => $selectedVariation->sku,
                    'price' => currencyConvert(
                        $selectedVariation->product->shopCountry?->currency,
                        $selectedVariation->price,
                        $userCurrency
                    ),
                    'image' => $selectedVariation->image,
                    'stock' => (int) $selectedVariation->stock,
                ] : null,
            ];
        })->toArray();

        return [
            'id' => (int) $this->resource->id,
            'order_no' => (string) $this->resource->order_no,
            'total_amount' => $totalConvertedAmount,
            'customer' => (object) [
                'id' => $this->resource->user?->id,
                'name' => trim("{$this->resource->user?->first_name} {$this->resource->user?->last_name}"),
                'email' => $this->resource->user?->email,
                'phone' => $this->resource->user?->phone,
            ],
            'products' => $products,
            'shipping_address' => (object) [
                'name' => "{$this->resource->user?->userShippingAddress()->first()?->first_name} {$this->resource->user?->userShippingAddress()->first()?->last_name}",
                'phone' => $this->resource->user?->userShippingAddress()->first()?->phone,
                'email' => $this->resource->user?->userShippingAddress()->first()?->email,
                'address' => $this->resource->user?->userShippingAddress()->first()?->street_address,
                'city' => $this->resource->user?->userShippingAddress()->first()?->city,
                'state' => $this->resource->user?->userShippingAddress()->first()?->state,
                'zip' => $this->resource->user?->userShippingAddress()->first()?->zip,
            ],
            'order_date' => Date::parse($this->resource->created_at)->format('d M Y'),
            'order_time' => Date::parse($this->resource->created_at)->format('h:i A'),
            'payment_status' => strtolower($this->resource->payment_status) === 'success' ? 'paid' : 'not-paid',
            'payment_method' => $this->resource->payment_method,
            'status' => $this->resource->status,
        ];
    }
}
