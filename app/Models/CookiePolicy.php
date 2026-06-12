<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $short_description
 * @property string|null $description
 * @property string|null $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookiePolicy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookiePolicy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookiePolicy query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookiePolicy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookiePolicy whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookiePolicy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookiePolicy whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookiePolicy whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CookiePolicy whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'short_description',
    'description',
    'status',
])]
class CookiePolicy extends Model
{
    use HasFactory;
}
