<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $table = 'attributes';

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(array $attributes, private readonly \Illuminate\Foundation\Application $application)
    {
        parent::__construct($attributes);
    }

    use HasFactory;

    protected $with = ['attribute_translations'];

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang === false ? $this->application->getLocale() : $lang;
        $attribute_translation = $this->attribute_translations->where('lang', $lang)->first();

        return $attribute_translation != null ? $attribute_translation->$field : $this->$field;
    }

    public function attribute_translations()
    {
        return $this->hasMany(AttributeTranslation::class);
    }

    public function attribute_values()
    {
        return $this->hasMany(AttributeValue::class);
    }
}
