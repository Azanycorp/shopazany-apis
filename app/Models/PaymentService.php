<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaymentService extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Country, $this, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'payment_service_country');
    }
}
