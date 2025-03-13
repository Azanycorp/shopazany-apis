<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'start_date',
        'end_date',
        'type',
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
