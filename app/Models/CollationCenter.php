<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $location
 * @property string $status
 * @property string|null $note
 * @property string|null $city
 * @property int|null $country_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Country|null $country
 * @property-read Collection<int, PickupStation> $hubs
 * @property-read int|null $hubs_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollationCenter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollationCenter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollationCenter query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollationCenter whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollationCenter whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollationCenter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollationCenter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollationCenter whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollationCenter whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollationCenter whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollationCenter whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollationCenter whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'name',
    'location',
    'status',
    'note',
    'city',
    'country_id',
])]
class CollationCenter extends Model
{
    /**
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function hubs(): HasMany
    {
        return $this->HasMany(PickupStation::class, 'collation_center_id');
    }
}
