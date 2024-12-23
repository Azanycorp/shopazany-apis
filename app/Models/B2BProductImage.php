<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class B2BProductImage extends Model
{
    protected $table = 'b2b_product_images';

    use HasFactory;

    protected $fillable = [
        'b2b_product_id',
        'image',
    ];

    public function b2bProduct()
    {
        return $this->belongsTo(B2BProduct::class);
    }
}
