<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingCountry extends Model
{
    protected $fillable = [
        'name',
        'code',
        'zone',
        'status',
    ];
}
