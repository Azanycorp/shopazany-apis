<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shippment extends Model
{
    protected $fillable = [
        'hub_id',
        'collation_id',
        'shippment_id',
        'order_number',
        'type',
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
        'reciever_name',
        'reciever_phone',
        'vehicle_number',
        'delivery_address',
        'transfer_reason',
        'item_condition',
        'logged_items',
    ];

    protected function casts(): array
    {
        return [
            'package' => 'array',
            'customer' => 'array',
            'vendor' => 'array',
            'logged_items' => 'array',
        ];
    }

    public function collationCentre()
    {
        return $this->belongsTo(CollationCenter::class, 'collation_id');
    }

    public function hub()
    {
        return $this->belongsTo(PickupStation::class, 'hub_id');
    }

    public function activities()
    {
        return $this->hasMany(ShippmentActivity::class, 'shippment_id');
    }
}
