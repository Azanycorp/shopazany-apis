<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class State extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'name',
    ];

    public function country(): BelongsTo
    {
        return $this->belongTo(Country::class, 'country_id');
    }
}
