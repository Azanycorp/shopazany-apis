<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickupStation extends Model
{
    protected $fillable = [
        'name',
        'location',
        'status',
        'note',
        'city',
        'country_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

}
