<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'sortname',
        'name',
        'phonecode'
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'country_id');
    }
}
