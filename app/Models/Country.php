<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Country extends Model
{
    use HasFactory;

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
