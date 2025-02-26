<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupStation extends Model
{
    protected $fillable = [
        'collation_center_id',
        'name',
        'location',
        'status',
        'note',
        'city',
        'country_id'
    ];
}
