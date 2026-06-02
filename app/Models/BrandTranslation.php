<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read Brand|null $brand
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BrandTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BrandTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BrandTranslation query()
 *
 * @mixin \Eloquent
 */
#[Fillable(['name', 'lang', 'brand_id'])]
#[Table(name: 'brand_translations')]
class BrandTranslation extends Model
{
    use HasFactory;

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
