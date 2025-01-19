<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kyc extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'date_of_birth',
        'nationality',
        'country_of_residence',
        'city',
        'phone_number',
        'document_number',
        'document_type',
        'image'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
