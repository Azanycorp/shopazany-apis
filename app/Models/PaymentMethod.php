<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'bank_name',
        'account_number',
        'account_name',
        'recipient_code',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array'
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
