<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        'admin_id',
        'title',
        'type',
        'image',
        'slug',
        'description',
    ];
    public function user()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
