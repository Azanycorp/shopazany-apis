<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $sortname
 * @property string $name
 * @property string|null $phonecode
 * @property int $is_allowed
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, PaymentService> $paymentServices
 * @property-read int|null $payment_services_count
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property-read ShopCountry|null $shopCountry
 *
 * @method static \Database\Factories\CountryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereIsAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country wherePhonecode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereSortname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'sortname',
    'name',
    'phonecode',
    'is_allowed',
])]
class Country extends Model
{
    use ClearsResponseCache, HasFactory;

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'country_id');
    }

    /**
     * @return HasOne<ShopCountry, $this>
     */
    public function shopCountry(): HasOne
    {
        return $this->hasOne(ShopCountry::class, 'country_id');
    }

    public function paymentServices()
    {
        return $this->belongsToMany(PaymentService::class, 'payment_service_country');
    }
}
