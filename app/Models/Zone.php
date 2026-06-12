<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read Collection<int, CarrierRangePrice> $carrierRangePrices
 * @property-read int|null $carrier_range_prices_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zone query()
 *
 * @mixin \Eloquent
 */
#[Fillable(['name', 'status'])]
#[Table(name: 'zones')]
class Zone extends Model
{
    use HasFactory;

    public function carrierRangePrices()
    {
        return $this->hasMany(CarrierRangePrice::class);
    }
}
