<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class B2bProductSubCategory extends Model
{
use ClearsResponseCache;

    protected $fillable = [
        'category_id',
        'name',
        'image',
        'slug',
        'status'
    ];

    public function category()
    {
        return $this->belongsTo(B2BProductCategory::class, 'category_id');
    }

    public function products():HasMany
    {
        return $this->hasMany(B2BProduct::class, 'sub_category_id');
    }

}
