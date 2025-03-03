<?php

namespace App\Models;

use App\Models\CollationCenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
    public function collationCenter(): BelongsTo
    {
        return $this->belongsTo(CollationCenter::class, 'collation_center_id');
    }
}
