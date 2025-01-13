<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessInformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_location',
        'business_type',
        'business_name',
        'business_reg_number',
        'business_phone',
        'country_id',
        'city',
        'address',
        'zip',
        'state',
        'apartment',
        'business_reg_document',
        'identification_type',
        'identification_type_document',
        'agree',
    ];

    protected function casts(): array
    {
        return [
            'agree' => 'boolean'
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
