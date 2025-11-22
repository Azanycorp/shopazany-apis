<?php

namespace App\Models;

use App\Enum\UserType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class B2bWishList extends Model
{
    protected $fillable = ['user_id', 'product_id', 'qty'];

    public function user()
    {
        return $this->belongsTo(User::class)->where('type', UserType::B2B_BUYER);
    }

    public function product()
    {
        return $this->belongsTo(B2BProduct::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\B2bProdctReview, $this>
     */
    public function b2bProductReview(): HasMany
    {
        return $this->hasMany(B2bProdctReview::class, 'product_id');
    }
}
