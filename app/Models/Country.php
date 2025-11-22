<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Country extends Model
{
    use ClearsResponseCache, HasFactory;

    protected $fillable = [
        'sortname',
        'name',
        'phonecode',
        'is_allowed',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'country_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\ShopCountry, $this>
     */
    public function shopCountry(): HasOne
    {
        return $this->hasOne(ShopCountry::class, 'country_id');
    }

    public function paymentServices()
    {
        return $this->belongsToMany(PaymentService::class, 'payment_service_country');
    }
}
