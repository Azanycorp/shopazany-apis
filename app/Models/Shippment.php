<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $hub_id
 * @property int|null $collation_id
 * @property string $shippment_id
 * @property string|null $order_number
 * @property string $type
 * @property array<array-key, mixed>|null $package
 * @property array<array-key, mixed>|null $customer
 * @property array<array-key, mixed>|null $vendor
 * @property string $status
 * @property string $priority
 * @property string|null $expected_delivery_date
 * @property string|null $start_origin
 * @property string|null $current_location
 * @property string|null $activity
 * @property string|null $note
 * @property float $items
 * @property array<array-key, mixed>|null $logged_items
 * @property string|null $dispatch_name
 * @property string|null $destination_name
 * @property string|null $dispatch_phone
 * @property string|null $expected_delivery_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $reciever_name
 * @property string|null $reciever_phone
 * @property string|null $vehicle_number
 * @property string|null $delivery_address
 * @property string|null $transfer_reason
 * @property string|null $item_condition
 * @property-read Collection<int, ShippmentActivity> $activities
 * @property-read int|null $activities_count
 * @property-read CollationCenter|null $collationCentre
 * @property-read PickupStation|null $hub
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereCollationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereCurrentLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereCustomer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereDeliveryAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereDestinationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereDispatchName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereDispatchPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereExpectedDeliveryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereExpectedDeliveryTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereHubId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereItemCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereLoggedItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment wherePackage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereRecieverName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereRecieverPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereShippmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereStartOrigin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereTransferReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereVehicleNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shippment whereVendor($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'hub_id',
    'collation_id',
    'shippment_id',
    'order_number',
    'type',
    'package',
    'customer',
    'vendor',
    'status',
    'priority',
    'expected_delivery_date',
    'start_origin',
    'current_location',
    'activity',
    'note',
    'items',
    'dispatch_name',
    'destination_name',
    'dispatch_phone',
    'expected_delivery_time',
    'reciever_name',
    'reciever_phone',
    'vehicle_number',
    'delivery_address',
    'transfer_reason',
    'item_condition',
    'logged_items',
])]
class Shippment extends Model
{
    protected function casts(): array
    {
        return [
            'package' => 'array',
            'customer' => 'array',
            'vendor' => 'array',
            'logged_items' => 'array',
        ];
    }

    public function collationCentre()
    {
        return $this->belongsTo(CollationCenter::class, 'collation_id');
    }

    public function hub()
    {
        return $this->belongsTo(PickupStation::class, 'hub_id');
    }

    public function activities()
    {
        return $this->hasMany(ShippmentActivity::class, 'shippment_id');
    }
}
