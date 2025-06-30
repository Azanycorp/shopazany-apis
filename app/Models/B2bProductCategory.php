<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;

class B2bProductCategory extends Model
{
    use ClearsResponseCache;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'featured',
        'meta_title',
        'meta_description',
    ];

    public function subcategory()
    {
        return $this->hasMany(B2bProductSubCategory::class, 'category_id');
    }

    public function products()
    {
        return $this->hasMany(B2BProduct::class, 'category_id');
    }
}
