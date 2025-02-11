<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopCountry extends Model
{
    use HasFactory, ClearsResponseCache;

    protected $fillable = [
        'country_id',
        'name',
        'flag',
        'currency'
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
