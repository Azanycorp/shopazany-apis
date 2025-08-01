<?php

namespace App\Models;

use App\Models\BatchActivity;
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
        'driver_phone',
        'departure',
        'arrival',
        'note',
        'origin_hub',
        'destination_hub',
        'weight',
    ];

    protected function casts(): array
    {
        return [
            'shippment_ids' => 'array',
        ];
    }

    public function getShippmentsAttribute()
    {
        return Shippment::whereIn('id', $this->shippment_ids ?? [])->get();
    }

    public function activities()
    {
        return $this->hasMany(BatchActivity::class, 'batch_id', 'id');
    }

    public function collationCentre()
    {
        return $this->belongsTo(CollationCenter::class, 'collation_id');
    }

    public function hub()
    {
        return $this->belongsTo(PickupStation::class, 'hub_id');
    }
}
