<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBusinessInformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_location',
        'business_type',
        'identity_type',
        'file',
        'confirm'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
