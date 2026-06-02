<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read Category|null $category
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryTranslation query()
 *
 * @mixin \Eloquent
 */
#[Fillable(['name', 'lang', 'category_id'])]
#[Table(name: 'category_translations')]
class CategoryTranslation extends Model
{
    use HasFactory;

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
