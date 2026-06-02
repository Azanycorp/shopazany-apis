<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $collation_id
 * @property string $batch_id
 * @property string $type
 * @property array<array-key, mixed>|null $shippment_ids
 * @property float $items
 * @property string $status
 * @property string $priority
 * @property string|null $destination_state
 * @property string|null $destination_centre
 * @property string|null $vehicle
 * @property string|null $driver_name
 * @property string|null $driver_phone
 * @property string|null $departure
 * @property string|null $arrival
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $origin_hub
 * @property string|null $destination_hub
 * @property string|null $weight
 * @property-read Collection<int, BatchActivity> $activities
 * @property-read int|null $activities_count
 * @property-read CollationCenter|null $collationCentre
 * @property-read PickupStation|null $hub
 * @property-read mixed $shippments
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereArrival($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereCollationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereDeparture($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereDestinationCentre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereDestinationHub($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereDestinationState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereDriverName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereDriverPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereOriginHub($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereShippmentIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereVehicle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentBatch whereWeight($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'collation_id',
    'shippment_ids',
    'batch_id',
    'items',
    'status',
    'priority',
    'destination_state',
    'destination_centre',
    'vehicle',
    'driver_name',
    'driver_phone',
    'departure',
    'arrival',
    'note',
    'origin_hub',
    'destination_hub',
    'weight',
])]
class ShippmentBatch extends Model
{
    protected function casts(): array
    {
        return [
            'shippment_ids' => 'array',
        ];
    }

    protected function shippments(): Attribute
    {
        return Attribute::make(get: function () {
            return Shippment::whereIn('id', $this->shippment_ids ?? [])->get();
        });
    }

    public function activities()
    {
        return $this->hasMany(BatchActivity::class, 'batch_id', 'id');
    }

    public function collationCentre()
    {
        return $this->belongsTo(CollationCenter::class, 'collation_id');
    }

    public function hub()
    {
        return $this->belongsTo(PickupStation::class, 'hub_id');
    }
}
