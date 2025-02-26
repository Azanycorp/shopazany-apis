<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;

class B2bQuote extends Model
{
    use ClearsResponseCache;
    protected $fillable = [
        'buyer_id',
        'product_id',
        'seller_id',
        'product_data',
        'qty',
    ];
    protected function casts(): array
    {
        return [
            'product_data' => 'array'
        ];
    }
}
