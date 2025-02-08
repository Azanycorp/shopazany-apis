<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingAgent extends Model
{
    protected $fillable = [
        'name',
        'type',
        'logo',
        'country_id',
        'account_email',
        'account_password',
        'api_live_key',
        'api_test_key',
        'status'
    ];

    protected function casts(): array
    {
        return [
            'country_id' => 'array',
        ];
    }
}
