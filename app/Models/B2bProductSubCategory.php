<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class B2bProductSubCategory extends Model
{
    use ClearsResponseCache;

    protected $fillable = [
        'category_id',
        'name',
        'image',
        'slug',
        'status',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\B2bProductCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(B2bProductCategory::class, 'category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\B2BProduct, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(B2BProduct::class, 'sub_category_id');
    }
}
