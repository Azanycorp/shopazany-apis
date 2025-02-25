<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;

class CollationCenter extends Model
{
    use ClearsResponseCache;
    protected $fillable = [
        'name',
        'location',
        'status',
        'note',
    ];
}
