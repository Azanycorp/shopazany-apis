<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $address_name
 * @property string $name
 * @property string $surname
 * @property string $email
 * @property string|null $phone
 * @property string|null $street
 * @property string|null $city
 * @property string|null $postal_code
 * @property int $state_id
 * @property int $country_id
 * @property bool $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Country|null $country
 * @property-read State|null $state
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BSellerShippingAddress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BSellerShippingAddress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BSellerShippingAddress query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BSellerShippingAddress whereAddressName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BSellerShippingAddress whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BSellerShippingAddress whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BSellerShippingAddress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BSellerShippingAddress whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BSellerShippingAddress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BSellerShippingAddress whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BSellerShippingAddress whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BSellerShippingAddress wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BSellerShippingAddress wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BSellerShippingAddress whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BSellerShippingAddress whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BSellerShippingAddress whereSurname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BSellerShippingAddress whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BSellerShippingAddress whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
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
])]
class B2BSellerShippingAddress extends Model
{
    use ClearsResponseCache;

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return BelongsTo<State, $this>
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
