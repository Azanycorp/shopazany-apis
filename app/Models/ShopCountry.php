<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string|null $currency
 */
class ShopCountry extends Model
{
    use ClearsResponseCache, HasFactory;

    protected $fillable = [
        'country_id',
        'name',
        'flag',
        'currency',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
