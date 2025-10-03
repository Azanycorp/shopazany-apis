<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippmentActivity extends Model
{
    protected $fillable = [
        'shippment_id',
        'comment',
        'note',
    ];

    protected $hidden = [
        'updated_at',
    ];
}
