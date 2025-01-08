<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2bWishList extends Model
{

    protected $fillable = ['user_id', 'product_id'];

    public function user()
    {
        return $this->belongsTo(User::class)->where('type','b2b_buyer');
    }

    public function product()
    {
        return $this->belongsTo(B2BProduct::class);
    }
}
