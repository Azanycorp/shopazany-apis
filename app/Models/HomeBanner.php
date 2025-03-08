<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeBanner extends Model
{
    protected $fillable = [
        'hero_banner',
        'banner_one',
        'banner_two',
        'banner_three',
        'banner_four',
        'banner_five',
    ];
}
