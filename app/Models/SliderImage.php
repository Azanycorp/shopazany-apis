<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SliderImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'type',
        'link'
    ];

    protected static function booted()
    {
        static::created(function ($slider): void {
            Cache::forget('home_sliders');

            Cache::rememberForever('home_sliders', function () {
                return SliderImage::orderBy('created_at', 'desc')->take(5)->get();
            });
        });
    }
}
