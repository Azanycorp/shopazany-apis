<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'image',
        'public_id',
        'position',
        'type',
    ];

    protected $hidden = [
        'public_id',
        'updated_at',
    ];

    public function banners()
    {
        return $this->hasMany(Banner::class, 'deal_id');
    }
}
