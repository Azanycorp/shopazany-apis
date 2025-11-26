<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Blog extends Model
{
    protected $fillable = [
        'admin_id',
        'title',
        'type',
        'image',
        'public_id',
        'slug',
        'description',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
