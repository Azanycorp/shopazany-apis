<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $location
 * @property string|null $note
 * @property string|null $city
 * @property int|null $country_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Country|null $country
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PickupStation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PickupStation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PickupStation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PickupStation whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PickupStation whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PickupStation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PickupStation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PickupStation whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PickupStation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PickupStation whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PickupStation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PickupStation whereUpdatedAt($value)
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
#[Hidden([
    'created_at',
    'updated_at',
])]
class PickupStation extends Model
{
    /**
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
