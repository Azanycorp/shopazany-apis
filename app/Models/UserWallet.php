<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model
{
    protected $fillable = [
        'user_id',
        'master_wallet',
        'transaction_wallet',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->where([
            'type' => 'b2b_seller'
        ]);
    }
}
