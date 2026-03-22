<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class B2bProdctLike extends Model
{
    protected $fillable = [
        'product_id',
        'buyer_id',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * @return BelongsTo<B2BProduct, $this>
     */
    public function b2bProduct(): BelongsTo
    {
        return $this->belongsTo(B2BProduct::class, 'product_id');
    }
}
