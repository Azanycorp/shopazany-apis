<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends Model
{
    use HasFactory, ClearsResponseCache;

    protected $fillable = [
        'sortname',
        'name',
        'phonecode',
        'is_allowed',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'country_id');
    }

    public function shopCountry(): HasOne
    {
        return $this->hasOne(ShopCountry::class, 'country_id');
    }

    public function paymentServices()
    {
        return $this->belongsToMany(PaymentService::class, 'payment_service_country');
    }
}
