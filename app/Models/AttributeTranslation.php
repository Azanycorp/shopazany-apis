<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeTranslation extends Model
{
    protected $table = 'attribute_translations';

    use HasFactory;

    protected $fillable = ['name', 'lang', 'attribute_id'];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
