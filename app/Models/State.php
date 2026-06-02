<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $country_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Country|null $country
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'country_id',
    'name',
])]
class State extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
