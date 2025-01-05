<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payout extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'fee',
        'account_name',
        'account_number',
        'bank',
        'status',
        'date_paid',
    ];

    function paymentInfo(): HasOne
    {
        return $this->hasOne(BankAccount::class, 'user_id', 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->where([
            'type' => 'b2b_seller'
        ]);
    }
}
