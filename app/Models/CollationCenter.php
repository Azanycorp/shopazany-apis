<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollationCenter extends Model
{
    use ClearsResponseCache;
    protected $fillable = [
        'name',
        'location',
        'status',
        'note',
        'city',
        'country_id',
    ];
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class,'country_id');
    }
    public function hubs(): HasMany
    {
        return $this->HasMany(PickupStation::class,'collation_center_id');
    }
}
