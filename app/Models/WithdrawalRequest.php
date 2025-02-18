<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_type',
        'amount',
        'previous_balance',
        'current_balance',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
