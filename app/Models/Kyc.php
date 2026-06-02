<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $date_of_birth
 * @property string $nationality
 * @property string $country_of_residence
 * @property string $city
 * @property string $phone_number
 * @property string $document_number
 * @property string $document_type
 * @property string $image
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kyc newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kyc newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kyc query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kyc whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kyc whereCountryOfResidence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kyc whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kyc whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kyc whereDocumentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kyc whereDocumentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kyc whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kyc whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kyc whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kyc whereNationality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kyc wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kyc whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kyc whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'name',
    'date_of_birth',
    'nationality',
    'country_of_residence',
    'city',
    'phone_number',
    'document_number',
    'document_type',
    'image',
])]
class Kyc extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
