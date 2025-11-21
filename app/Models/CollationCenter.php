<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollationCenter extends Model
{
    protected $fillable = [
        'name',
        'location',
        'status',
        'note',
        'city',
        'country_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function hubs(): HasMany
    {
        return $this->HasMany(PickupStation::class, 'collation_center_id');
    }
}
