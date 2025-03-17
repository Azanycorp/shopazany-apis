<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerOrderDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $userCurrency = $user?->default_currency ?? 'USD';

        $totalConvertedAmount = 0;

        $products = $this->products->map(function ($product) use ($userCurrency, &$totalConvertedAmount) {
            $productCurrency = optional($product->shopCountry)->currency ?? 'USD';

            $convertedSubTotal = currencyConvert(
                $productCurrency,
                $product->pivot->sub_total,
                $userCurrency
            );

            $totalConvertedAmount += $convertedSubTotal;

            return [
                'id' => (int) $product->id,
                'name' => (string) $product->name,
                'description' => (string) $product->description,
                'price' => (float) $product->pivot->price,
                'quantity' => (int) $product->pivot->product_quantity,
                'sub_total' => (float) $convertedSubTotal,
                'original_currency' => (string) $productCurrency,
                'image' => (string) $product->image,
                'status' => (string) $product->pivot->status,
            ];
        })->toArray();

        return [
            'id' => (int)$this->id,
            'order_no' => (string)$this->order_no,
            'total_amount' => $totalConvertedAmount,
            'customer' => (object) [
                'id' => $this->user?->id,
                'name' => trim("{$this->user?->first_name} {$this->user?->last_name}"),
                'email' => $this->user?->email,
                'phone' => $this->user?->phone,
            ],
            'products' => $products,
            'shipping_address' => (object) [
                'name' => $this->user?->userShippingAddress()->first()?->first_name . ' ' . $this->user?->userShippingAddress()->first()?->last_name,
                'phone' => $this->user?->userShippingAddress()->first()?->phone,
                'email' => $this->user?->userShippingAddress()->first()?->email,
                'address' => $this->user?->userShippingAddress()->first()?->street_address,
                'city' => $this->user?->userShippingAddress()->first()?->city,
                'state' => $this->user?->userShippingAddress()->first()?->state,
                'zip' => $this->user?->userShippingAddress()->first()?->zip,
            ],
            'activities' => $this->orderActivities->map(function ($activity) {
                return [
                    'message' => $activity->message,
                    'status' => $activity->status,
                    'date' => Carbon::parse($activity->date)->format('d M Y h:i A'),
                ];
            }),
            'order_date' => Carbon::parse($this->created_at)->format('d M Y'),
            'order_time' => Carbon::parse($this->created_at)->format('h:i A'),
            'payment_status' => strtolower($this->payment_status) === "success" ? "paid" : "not-paid",
            'payment_method' => $this->payment_method,
            'status' => $this->status,
        ];
    }
}
