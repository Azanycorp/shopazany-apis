<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialSetting extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'url',
    ];
}
