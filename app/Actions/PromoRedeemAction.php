<?php

namespace App\Actions;

use App\Models\PromoRedemption;

final readonly class PromoRedeemAction
{
    public function handle(int $userId, int $promoId, int $productId): void
    {
        PromoRedemption::create([
            'user_id' => $userId,
            'promo_id' => $promoId,
            'product_id' => $productId,
        ]);
    }
}
