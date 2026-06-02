<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $country_id
 * @property string $name
 * @property string $flag
 * @property string|null $currency
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Country|null $country
 *
 * @method static \Database\Factories\ShopCountryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCountry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCountry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCountry query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCountry whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCountry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCountry whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCountry whereFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCountry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCountry whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCountry whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'country_id',
    'name',
    'flag',
    'currency',
])]
class ShopCountry extends Model
{
    use ClearsResponseCache, HasFactory;

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
