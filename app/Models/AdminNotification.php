<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $fillable = ['title', 'content', 'is_read'];

    protected $casts = [
        'is_read' => 'boolean',
    ];
}
