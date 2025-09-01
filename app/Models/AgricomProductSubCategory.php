<?php

namespace App\Models;

use App\Models\AgricomProductCategory;
use Illuminate\Database\Eloquent\Model;

class AgricomProductSubCategory extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'image',
        'slug',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(AgricomProductCategory::class, 'category_id');
    }

}
