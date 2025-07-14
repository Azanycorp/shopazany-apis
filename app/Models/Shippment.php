<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shippment extends Model
{
    protected $fillable = [
        'hub_id',
        'collation_id',
        'shippment_id',
        'package',
        'customer',
        'vendor',
        'status',
        'priority',
        'expected_delivery_date',
        'start_origin',
        'current_location',
        'activity',
        'note',
        'items',
        'dispatch_name',
        'destination_name',
        'dispatch_phone',
        'expected_delivery_time',
    ];
}
