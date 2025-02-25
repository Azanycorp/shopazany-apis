<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollationCenter extends Model
{
    protected $fillable = [
        'name',
        'location',
        'status',
        'note',
    ];
}
