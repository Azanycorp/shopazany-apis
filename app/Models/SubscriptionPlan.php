<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $cost
 * @property string $country_id
 * @property string|null $currency
 * @property string $period
 * @property int $tier
 * @property array<array-key, mixed>|null $tagline
 * @property string|null $designation
 * @property string|null $details
 * @property string|null $type
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $product_limit
 * @property-read Collection<int, UserSubcription> $subscriptions
 * @property-read int|null $subscriptions_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereDesignation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan wherePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereProductLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereTagline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereTier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SubscriptionPlan whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'title',
    'cost',
    'country_id',
    'period',
    'designation',
    'tagline',
    'details',
    'status',
    'tier',
    'currency',
    'type',
    'product_limit',
])]
class SubscriptionPlan extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'tagline' => 'array',
        ];
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubcription::class, 'subscription_plan_id');
    }
}
