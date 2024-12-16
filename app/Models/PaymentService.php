<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentService extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function countries()
    {
        return $this->belongsToMany(Country::class, 'payment_service_country');
    }
}
