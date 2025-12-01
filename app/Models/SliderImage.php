<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SliderImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'public_id',
        'type',
        'link',
    ];

    protected static function booted()
    {
        static::created(function ($slider): void {
            cache()->forget('home_sliders');

            cache()->rememberForever('home_sliders', function () {
                return SliderImage::orderBy('created_at', 'desc')->take(5)->get();
            });
        });
    }
}
