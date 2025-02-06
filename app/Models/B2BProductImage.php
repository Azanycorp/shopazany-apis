<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class B2BProductImage extends Model
{
    protected $table = 'b2b_product_images';

    use HasFactory,ClearsResponseCache;

    protected $fillable = [
        'b2b_product_id',
        'image',
    ];

    public function b2bProduct()
    {
        return $this->belongsTo(B2BProduct::class);
    }
}
