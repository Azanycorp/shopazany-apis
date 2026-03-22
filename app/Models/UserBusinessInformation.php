<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBusinessInformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_location',
        'business_type',
        'identity_type',
        'file',
        'confirm',
        'status',
        'how_to_sell',
        'business_logo',
        'business_banner',
        'min_order_amount',
        'opening_time',
        'closing_time',
        'estimated_delivery_days',
        'order_prefix',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
