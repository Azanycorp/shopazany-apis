<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippmentBatch extends Model
{
    protected $fillable = [
        'collation_id',
        'shippment_ids',
        'batch_id',
        'items',
        'status',
        'priority',
        'destination_state',
        'destination_centre',
        'vehicle',
        'driver_name',
        'departure',
        'arrival',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'shippment_ids' => 'array',
        ];
    }
}
