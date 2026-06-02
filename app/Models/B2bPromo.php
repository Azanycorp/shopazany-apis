<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $coupon_code
 * @property float $discount
 * @property string|null $discount_type
 * @property string $type
 * @property string|null $start_date
 * @property string|null $end_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read B2BPromoProduct|null $b2bPromoProduct
 * @property-read B2BPromoTotalOrder|null $b2bTotalOrder
 * @property-read B2BPromoWelcomeCoupon|null $b2bWelcomeCoupon
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bPromo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bPromo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bPromo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bPromo whereCouponCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bPromo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bPromo whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bPromo whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bPromo whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bPromo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bPromo whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bPromo whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bPromo whereUpdatedAt($value)
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
])]
class B2bPromo extends Model
{
    use ClearsResponseCache;

    public function b2bPromoProduct()
    {
        return $this->hasOne(B2BPromoProduct::class);
    }

    public function b2bTotalOrder()
    {
        return $this->hasOne(B2BPromoTotalOrder::class);
    }

    public function b2bWelcomeCoupon()
    {
        return $this->hasOne(B2BPromoWelcomeCoupon::class);
    }
}
