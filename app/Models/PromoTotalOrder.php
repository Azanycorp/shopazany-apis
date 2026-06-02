<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $promo_id
 * @property numeric $minimum_cart_amount
 * @property numeric $maximum_discount_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoTotalOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoTotalOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoTotalOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoTotalOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoTotalOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoTotalOrder whereMaximumDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoTotalOrder whereMinimumCartAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoTotalOrder wherePromoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoTotalOrder whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'promo_id',
    'minimum_cart_amount',
    'maximum_discount_amount',
])]
#[Table(name: 'promo_total_orders')]
class PromoTotalOrder extends Model
{
    use HasFactory;
}
