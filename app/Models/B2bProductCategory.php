<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class B2bProductCategory extends Model
{
    use ClearsResponseCache;

    protected $fillable = [
        'name',
        'type',
        'slug',
        'image',
        'featured',
        'meta_title',
        'meta_description',
    ];

    public function subcategory(): HasMany
    {
        return $this->hasMany(B2bProductSubCategory::class, 'category_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(B2BProduct::class, 'category_id');
    }
}
