<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string|null $logo
 * @property array<array-key, mixed> $country_ids
 * @property string|null $account_email
 * @property string|null $account_password
 * @property string|null $api_live_key
 * @property string|null $api_test_key
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingAgent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingAgent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingAgent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingAgent whereAccountEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingAgent whereAccountPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingAgent whereApiLiveKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingAgent whereApiTestKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingAgent whereCountryIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingAgent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingAgent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingAgent whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingAgent whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingAgent whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingAgent whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippingAgent whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'name',
    'type',
    'logo',
    'country_ids',
    'account_email',
    'account_password',
    'api_live_key',
    'api_test_key',
    'status',
])]
class ShippingAgent extends Model
{
    protected function casts(): array
    {
        return [
            'country_ids' => 'array',
        ];
    }
}
