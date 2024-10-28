<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class B2BSellerShippingAddress extends Model
{
    protected $fillable = [
        'user_id',
        'address_name',
        'name',
        'surname',
        'email',
        'phone',
        'street',
        'city',
        'postal_code',
        'state_id',
        'country_id',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean'
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
