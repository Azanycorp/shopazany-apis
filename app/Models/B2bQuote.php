<?php

namespace App\Models;

use App\Enum\UserType;
use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
            'product_data' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'buyer_id')->where('type', UserType::B2B_BUYER);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id')->where('type', UserType::B2B_SELLER);
    }

    public function product()
    {
        return $this->belongsTo(B2BProduct::class);
    }

    public function b2bProductReview(): HasMany
    {
        return $this->hasMany(B2bProdctReview::class, 'product_id');
    }
}
