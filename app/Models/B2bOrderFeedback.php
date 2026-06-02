<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $seller_id
 * @property int|null $buyer_id
 * @property string $order_no
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderFeedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderFeedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderFeedback query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderFeedback whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderFeedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderFeedback whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderFeedback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderFeedback whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderFeedback whereSellerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bOrderFeedback whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class B2bOrderFeedback extends Model
{
    //
}
