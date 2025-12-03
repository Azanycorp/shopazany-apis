<?php

namespace App\Actions;

use App\Models\Order;

class CalculateOrderSummaryAction
{
    /**
     * Handle the order total calculation logic.
     */
    public function handle(Order $order, string $userCurrency): array
    {
        $totalConverted = 0;
        $discountTotal = 0;

        foreach ($order->products as $product) {
            $totalConverted += $product->pivot->sub_total;
            $discountTotal += $product->discount_value;
        }

        $shipping = 0;
        $tax = 0;
        $pointReward = 0;

        $summaryTotal = max(0, ($totalConverted) + $shipping + $tax);

        return [
            'sub_total' => $totalConverted,
            'discount' => $discountTotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'point_reward' => $pointReward,
            'total' => $summaryTotal,
        ];
    }
}
