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
 * @property numeric $minimum_shopping_amount
 * @property int $number_of_days_valid
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoWelcomeCoupon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoWelcomeCoupon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoWelcomeCoupon query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoWelcomeCoupon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoWelcomeCoupon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoWelcomeCoupon whereMinimumShoppingAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoWelcomeCoupon whereNumberOfDaysValid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoWelcomeCoupon wherePromoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoWelcomeCoupon whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'promo_id',
    'minimum_shopping_amount',
    'number_of_days_valid',
])]
#[Table(name: 'promo_welcome_coupons')]
class PromoWelcomeCoupon extends Model
{
    use HasFactory;
}
