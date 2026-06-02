<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $business_location
 * @property string $business_type
 * @property string $identity_type
 * @property string $file
 * @property int $confirm
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $how_to_sell
 * @property string|null $business_logo
 * @property string|null $business_banner
 * @property int $min_order_amount
 * @property string|null $opening_time
 * @property string|null $closing_time
 * @property int|null $estimated_delivery_days
 * @property string|null $order_prefix
 * @property string|null $description
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation whereBusinessBanner($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation whereBusinessLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation whereBusinessLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation whereBusinessType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation whereClosingTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation whereConfirm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation whereEstimatedDeliveryDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation whereHowToSell($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation whereIdentityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation whereMinOrderAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation whereOpeningTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation whereOrderPrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBusinessInformation whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'business_location',
    'business_type',
    'identity_type',
    'file',
    'confirm',
    'status',
    'how_to_sell',
    'business_logo',
    'business_banner',
    'min_order_amount',
    'opening_time',
    'closing_time',
    'estimated_delivery_days',
    'order_prefix',
    'description',
])]
class UserBusinessInformation extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
