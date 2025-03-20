<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory, ClearsResponseCache;

    protected $fillable = [
        'title',
        'slug',
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
