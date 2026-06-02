<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property string $message
 * @property string $status
 * @property string $current_location
 * @property string $date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStage whereCurrentLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStage whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStage whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStage whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStage whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderStage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'order_id',
    'message',
    'status',
    'current_location',
    'date',
])]
#[Hidden(['created_at', 'updated_at'])]
class OrderStage extends Model {}
