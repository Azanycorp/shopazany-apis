<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionWallet extends Model
{
    protected $fillable = [
        'user_id',
        'payment_id',
        'type',
        'credit',
        'debit',
        'remark',
        'funding_pop',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->where([
            'type' => 'b2b_seller',
        ]);
    }
}
