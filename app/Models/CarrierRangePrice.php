<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read Carrier|null $carrier
 * @property-read CarrierRange|null $carrier_ranges
 * @property-read Zone|null $zone
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarrierRangePrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarrierRangePrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarrierRangePrice query()
 *
 * @mixin \Eloquent
 */
#[Table(name: 'carrier_range_prices')]
class CarrierRangePrice extends Model
{
    use HasFactory;

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function carrier_ranges()
    {
        return $this->belongsTo(CarrierRange::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
