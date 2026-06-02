<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $code
 * @property string $status
 * @property string|null $zone
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingCountry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingCountry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingCountry query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingCountry whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingCountry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingCountry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingCountry whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingCountry whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingCountry whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingCountry whereZone($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'name',
    'code',
    'zone',
    'status',
])]
class ShippingCountry extends Model {}
