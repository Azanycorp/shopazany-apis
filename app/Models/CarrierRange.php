<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrierRange extends Model
{
    protected $table = "carrier_ranges";
    
    use HasFactory;

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function carrier_range_prices(){
    	return $this->hasMany(CarrierRangePrice::class);
    }
}
