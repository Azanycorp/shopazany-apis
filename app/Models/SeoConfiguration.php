<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'keywords',
        'description',
        'social_title',
        'social_description',
        'image'
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
        ];
    }
}
