<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandTranslation extends Model
{
    protected $table = "brand_translations";
    
    use HasFactory;

    protected $fillable = ['name', 'lang', 'brand_id'];

    public function brand(){
        return $this->belongsTo(Brand::class);
    }
}
