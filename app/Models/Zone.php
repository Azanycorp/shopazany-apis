<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $table = 'zones';

    use HasFactory;

    protected $fillable = ['name', 'status'];

    public function carrierRangePrices()
    {
        return $this->hasMany(CarrierRangePrice::class);
    }
}
