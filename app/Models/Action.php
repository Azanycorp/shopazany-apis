<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'points',
        'description',
        'icon',
        'verification_type',
        'country_ids',
        'default',
    ];

    protected function casts(): array
    {
        return [
            'country_ids' => 'array',
            'default' => 'boolean',
        ];
    }
}
