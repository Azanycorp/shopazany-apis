<?php

namespace App\Models;

use App\Enum\UserType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyerShippingAddress extends Model
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->where('type', UserType::B2B_BUYER);
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
