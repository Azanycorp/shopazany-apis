<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    use HasFactory;

    protected $fillable = [
        'heading_one',
        'sub_text_one',
        'heading_two',
        'sub_text_two',
        'image_one',
        'image_two',
    ];
}
