<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $b2b_promo_id
 * @property numeric $minimum_cart_amount
 * @property numeric $maximum_discount_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoTotalOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoTotalOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoTotalOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoTotalOrder whereB2bPromoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoTotalOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoTotalOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoTotalOrder whereMaximumDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoTotalOrder whereMinimumCartAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoTotalOrder whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'promo_id',
    'minimum_cart_amount',
    'maximum_discount_amount',
])]
#[Table(name: 'b2b_promo_total_orders')]
class B2BPromoTotalOrder extends Model {}
