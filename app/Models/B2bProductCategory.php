<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2bProductCategory extends Model
{

    protected $fillable = [
        'name',
        'slug',
        'image',
        'featured',
        'meta_title',
        'meta_description'
    ];

    // public function subcategory()
    // {
    //     return $this->hasMany(SubCategory::class, 'category_id');
    // }

    public function products()
    {
        return $this->hasMany(B2BProduct::class);
    }
}
