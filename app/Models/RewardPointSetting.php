<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardPointSetting extends Model
{
    protected $fillable = [
        'point',
        'value',
        'currency',
    ];
}
