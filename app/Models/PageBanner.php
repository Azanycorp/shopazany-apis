<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageBanner extends Model
{
    protected $fillable = [
        'page',
        'section',
        'type',
        'banner_url',
    ];
}
