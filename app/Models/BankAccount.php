<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_name',
        'bank_name',
        'account_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
