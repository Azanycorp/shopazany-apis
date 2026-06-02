<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property string $message
 * @property string $status
 * @property string $date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order $order
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderActivity query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderActivity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderActivity whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderActivity whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderActivity whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderActivity whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderActivity whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['order_id', 'message', 'status', 'date'])]
class OrderActivity extends Model
{
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
