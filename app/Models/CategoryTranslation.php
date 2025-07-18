<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryTranslation extends Model
{
    protected $table = 'category_translations';

    use HasFactory;

    protected $fillable = ['name', 'lang', 'category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
