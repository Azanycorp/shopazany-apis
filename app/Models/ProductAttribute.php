<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'value',
        'use_for_variation',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'use_for_variation' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
