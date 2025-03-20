<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        'admin_id',
        'title',
        'image',
        'description',
    ];
    public function user()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
