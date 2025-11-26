<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Banner, $this>
     */
    public function banners(): HasMany
    {
        return $this->hasMany(Banner::class, 'deal_id');
    }
}
