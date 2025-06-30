<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'data',
        'method',
        'status',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }
}
