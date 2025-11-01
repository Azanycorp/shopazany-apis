<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2CBlog extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'b2_c_blog_category_id',
        'short_description',
        'description',
        'image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'meta_image',
        'status',
        'created_by',
        'type',
    ];

    public function blogCategory()
    {
        return $this->belongsTo(B2CBlogCategory::class, 'b2_c_blog_category_id');
    }
}
