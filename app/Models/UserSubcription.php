<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $subscription_plan_id
 * @property int $payment_id
 * @property string $plan_start
 * @property string $plan_end
 * @property array<array-key, mixed>|null $authorization_data
 * @property string|null $subscription_type
 * @property string $status
 * @property string|null $expired_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SubscriptionPlan|null $subscriptionPlan
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubcription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubcription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubcription query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubcription whereAuthorizationData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubcription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubcription whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubcription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubcription wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubcription wherePlanEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubcription wherePlanStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubcription whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubcription whereSubscriptionPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubcription whereSubscriptionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubcription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSubcription whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'subscription_plan_id',
    'payment_id',
    'plan_start',
    'plan_end',
    'status',
    'subscription_type',
    'authorization_data',
    'expired_at',
])]
#[Hidden([
    'authorization_data',
])]
class UserSubcription extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'authorization_data' => 'json',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<SubscriptionPlan, $this>
     */
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }
}
