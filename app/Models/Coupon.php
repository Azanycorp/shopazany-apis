<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'name',
        'code',
        'link',
        'max_use',
        'used',
        'used_by',
        'type',
        'expire_at',
        'status',
        'total_used',
    ];

    protected function casts(): array
    {
        return [
            'used_by' => 'array'
        ];
    }
}
