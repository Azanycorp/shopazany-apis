<?php

namespace App\Models;

use App\Enum\UserType;
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
 * @property int $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Country|null $country
 * @property-read State|null $state
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerShippingAddress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerShippingAddress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerShippingAddress query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerShippingAddress whereAddressName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerShippingAddress whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerShippingAddress whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerShippingAddress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerShippingAddress whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerShippingAddress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerShippingAddress whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerShippingAddress whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerShippingAddress wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerShippingAddress wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerShippingAddress whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerShippingAddress whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerShippingAddress whereSurname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerShippingAddress whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BuyerShippingAddress whereUserId($value)
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
class BuyerShippingAddress extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->where('type', UserType::B2B_BUYER);
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
