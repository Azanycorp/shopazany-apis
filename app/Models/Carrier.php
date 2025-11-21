<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrier extends Model
{
    protected $table = 'carriers';

    use HasFactory;

    public function carrier_ranges()
    {
        return $this->hasMany(CarrierRange::class);
    }

    public function carrier_range_prices()
    {
        return $this->hasMany(CarrierRangePrice::class);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function active($query)
    {
        return $query->where('status', 1);
    }
}
