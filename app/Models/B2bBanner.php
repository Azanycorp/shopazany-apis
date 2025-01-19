<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2bBanner extends Model
{
    protected $fillable = [
        'title',
        'image',
        'start_date',
        'end_date',
        'products',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'products' => 'array'
        ];
    }
}
