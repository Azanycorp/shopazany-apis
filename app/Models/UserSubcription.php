<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubcription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'payment_id',
        'plan_start',
        'plan_end',
        'status',
        'authorization_data',
    ];

    protected function casts(): array
    {
        return [
            'authorization_data' => 'json'
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
