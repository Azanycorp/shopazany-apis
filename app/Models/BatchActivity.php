<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchActivity extends Model
{
     protected $fillable = [
        'batch_id',
        'comment',
        'note',
    ];

    protected $hidden = [
        'updated_at'
    ];
}
