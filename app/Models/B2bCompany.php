<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2bCompany extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'tax_id',
        'business_reg_number',
        'business_phone',
        'company_size',
        'website',
        'service_type',
        'average_spend',
        'country_id',
        'city',
        'address',
        'state',
        'logo'
    ];

    protected function casts(): array
    {
        return [
            'service_type' => 'array'
        ];
    }
}
