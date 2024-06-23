<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Brand extends Model
{
    protected $table = "brands";

    use HasFactory;

    protected $with = ['brand_translations'];

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang === false ? App::getLocale() : $lang;
        $brand_translation = $this->brand_translations->where('lang', $lang)->first();
        return $brand_translation != null ? $brand_translation->$field : $this->$field;
    }

    public function brand_translations()
    {
        return $this->hasMany(BrandTranslation::class);
    }

    public function brandLogo()
    {
        return $this->belongsTo(Upload::class, 'logo');
    }
}
