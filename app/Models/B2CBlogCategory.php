<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2CBlogCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function blogs()
    {
        return $this->hasMany(B2CBlog::class, 'b2_c_blog_category_id');
    }
}
