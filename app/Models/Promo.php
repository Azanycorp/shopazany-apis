<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $coupon_code
 * @property string $discount
 * @property string $discount_type
 * @property string $type
 * @property string $start_date
 * @property string $end_date
 * @property string $min_order_value
 * @property string $max_order_value
 * @property string $status
 * @property int $user_id
 */
class Promo extends Model
{
    use HasFactory;

    protected $fillable = [
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
    ];

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
