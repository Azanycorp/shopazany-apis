<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class B2bProdctReview extends Model
{
    protected $fillable = [
        'product_id',
        'buyer_id',
        'rating',
        'title',
        'note',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}
