<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Country> $countries
 * @property-read int|null $countries_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentService query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentService whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentService whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentService whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentService whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'name',
    'slug',
])]
class PaymentService extends Model
{
    /**
     * @return BelongsToMany<Country, $this, Pivot>
     */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'payment_service_country');
    }
}
