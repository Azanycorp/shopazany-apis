<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgricomProductCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'image',
        'featured',
        'meta_title',
        'meta_description',
    ];

    public function subcategory()
    {
        return $this->hasMany(B2bProductSubCategory::class, 'category_id');
    }
}
