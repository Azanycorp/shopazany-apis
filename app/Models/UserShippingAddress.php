<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserShippingAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'street_address',
        'state',
        'city',
        'zip',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
