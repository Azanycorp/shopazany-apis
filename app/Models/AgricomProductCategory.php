<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AgricomProductSubCategory;

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
        return $this->hasMany(AgricomProductSubCategory::class, 'category_id');
    }
}
