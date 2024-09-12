<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'cost',
        'country_id',
        'period',
        'tagline',
        'details',
        'status',
    ];
}
