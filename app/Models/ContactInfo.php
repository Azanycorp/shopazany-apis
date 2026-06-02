<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $email
 * @property array<array-key, mixed>|null $social_media
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactInfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactInfo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactInfo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactInfo whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactInfo whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactInfo wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactInfo whereSocialMedia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactInfo whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'address',
    'phone',
    'email',
    'social_media',
])]
class ContactInfo extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'social_media' => 'array',
        ];
    }
}
