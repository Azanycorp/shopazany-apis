<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read Collection<int, CarrierRangePrice> $carrier_range_prices
 * @property-read int|null $carrier_range_prices_count
 * @property-read Collection<int, CarrierRange> $carrier_ranges
 * @property-read int|null $carrier_ranges_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carrier active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carrier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carrier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carrier query()
 *
 * @mixin \Eloquent
 */
#[Table(name: 'carriers')]
class Carrier extends Model
{
    use HasFactory;

    public function carrier_ranges()
    {
        return $this->hasMany(CarrierRange::class);
    }

    public function carrier_range_prices()
    {
        return $this->hasMany(CarrierRangePrice::class);
    }

    #[Scope]
    protected function active($query)
    {
        return $query->where('status', 1);
    }
}
