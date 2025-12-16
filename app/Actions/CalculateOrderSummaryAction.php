<?php

namespace App\Actions;

use App\Models\Order;

class CalculateOrderSummaryAction
{
    /**
     * Handle the order total calculation logic.
     */
    public function handle(Order $order, $user): array
    {
        $shipping = 0;
        $tax = 0;
        $pointReward = 0;

        $totalConverted = $order->products->sum(function ($product) use ($user): float {
            return currencyConvert(
                $product->shopCountry->currency ?? 'USD',
                $product->pivot->sub_total,
                $user->default_currency
            );
        });

        $discountTotal = $order->products->sum(function ($product) use ($user): float {
            return currencyConvert(
                $product->shopCountry->currency ?? 'USD',
                $product->discount_value,
                $user->default_currency
            );
        });

        $summaryTotal = max(0, ($totalConverted) + $shipping + $tax);

        return [
            'sub_total' => number_format($totalConverted, 2),
            'discount' => number_format($discountTotal, 2),
            'shipping' => number_format($shipping, 2),
            'tax' => number_format($tax, 2),
            'point_reward' => number_format($pointReward, 2),
            'total' => number_format($summaryTotal, 2),
        ];
    }
}
