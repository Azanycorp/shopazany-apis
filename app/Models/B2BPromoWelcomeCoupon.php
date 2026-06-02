<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $b2b_promo_id
 * @property numeric $minimum_shopping_amount
 * @property int $number_of_days_valid
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoWelcomeCoupon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoWelcomeCoupon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoWelcomeCoupon query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoWelcomeCoupon whereB2bPromoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoWelcomeCoupon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoWelcomeCoupon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoWelcomeCoupon whereMinimumShoppingAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoWelcomeCoupon whereNumberOfDaysValid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoWelcomeCoupon whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'promo_id',
    'minimum_shopping_amount',
    'number_of_days_valid',
])]
#[Table(name: 'b2b_promo_welcome_coupons')]
class B2BPromoWelcomeCoupon extends Model {}
