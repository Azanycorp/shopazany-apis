<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStage extends Model
{
    protected $fillable = [
        'order_id',
        'message',
        'status',
        'current_location',
        'date',
    ];

    protected $hidden = ['created_at', 'updated_at'];
}
