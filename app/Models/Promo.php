<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $coupon_code
 * @property int $discount
 * @property string $discount_type
 * @property string $type
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $user_id
 * @property numeric|null $min_order_value
 * @property numeric|null $max_order_value
 * @property string|null $status
 * @property-read PromoProduct|null $promoProduct
 * @property-read Collection<int, PromoRedemption> $redemptions
 * @property-read int|null $redemptions_count
 * @property-read PromoTotalOrder|null $totalOrder
 * @property-read User|null $user
 * @property-read PromoWelcomeCoupon|null $welcomeCoupon
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promo whereCouponCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promo whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promo whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promo whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promo whereMaxOrderValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promo whereMinOrderValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promo whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promo whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promo whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Promo whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'coupon_code',
    'discount',
    'discount_type',
    'type',
    'start_date',
    'end_date',
    'user_id',
    'min_order_value',
    'max_order_value',
    'status',
])]
class Promo extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'min_order_value' => 'decimal:2',
            'max_order_value' => 'decimal:2',
        ];
    }

    public function promoProduct(): HasOne
    {
        return $this->hasOne(PromoProduct::class);
    }

    public function totalOrder(): HasOne
    {
        return $this->hasOne(PromoTotalOrder::class);
    }

    public function welcomeCoupon(): HasOne
    {
        return $this->hasOne(PromoWelcomeCoupon::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(PromoRedemption::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
