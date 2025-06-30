<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class B2BProductImage extends Model
{
    protected $table = 'b2b_product_images';

    use ClearsResponseCache, HasFactory;

    protected $fillable = [
        'b2b_product_id',
        'image',
        'public_id',
    ];

    public function b2bProduct()
    {
        return $this->belongsTo(B2BProduct::class);
    }
}
