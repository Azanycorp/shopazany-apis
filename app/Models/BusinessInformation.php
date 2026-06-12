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
 * @property string $business_location
 * @property string $business_type
 * @property string|null $business_name
 * @property string|null $business_reg_number
 * @property string|null $business_phone
 * @property int|null $country_id
 * @property string|null $city
 * @property string|null $address
 * @property string|null $zip
 * @property string|null $state
 * @property string|null $apartment
 * @property string|null $business_reg_document
 * @property string $identification_type
 * @property string $identification_type_document
 * @property bool $agree
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $logo
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereAgree($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereApartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereBusinessLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereBusinessName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereBusinessPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereBusinessRegDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereBusinessRegNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereBusinessType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereIdentificationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereIdentificationTypeDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BusinessInformation whereZip($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
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
])]
class BusinessInformation extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'agree' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
