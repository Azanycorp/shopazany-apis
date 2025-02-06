<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;

class B2bBanner extends Model
{
    use ClearsResponseCache;
    
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
