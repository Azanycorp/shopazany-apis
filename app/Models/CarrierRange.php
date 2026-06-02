<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read Carrier|null $carrier
 * @property-read Collection<int, CarrierRangePrice> $carrier_range_prices
 * @property-read int|null $carrier_range_prices_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarrierRange newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarrierRange newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarrierRange query()
 *
 * @mixin \Eloquent
 */
#[Table(name: 'carrier_ranges')]
class CarrierRange extends Model
{
    use HasFactory;

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function carrier_range_prices()
    {
        return $this->hasMany(CarrierRangePrice::class);
    }
}
