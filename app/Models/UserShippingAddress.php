<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $street_address
 * @property string|null $state
 * @property string|null $city
 * @property string|null $zip
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserShippingAddress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserShippingAddress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserShippingAddress query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserShippingAddress whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserShippingAddress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserShippingAddress whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserShippingAddress whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserShippingAddress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserShippingAddress whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserShippingAddress wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserShippingAddress whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserShippingAddress whereStreetAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserShippingAddress whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserShippingAddress whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserShippingAddress whereZip($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'first_name',
    'last_name',
    'email',
    'phone',
    'street_address',
    'state',
    'city',
    'zip',
])]
class UserShippingAddress extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
