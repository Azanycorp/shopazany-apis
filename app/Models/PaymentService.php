<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PaymentService extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * @return BelongsToMany<Country, $this, Pivot>
     */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'payment_service_country');
    }
}
