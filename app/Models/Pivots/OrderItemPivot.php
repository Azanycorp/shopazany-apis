<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int|null $variation_id
 * @property int $product_quantity
 * @property float $price
 * @property float $sub_total
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $status
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemPivot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemPivot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemPivot query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemPivot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemPivot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemPivot whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemPivot wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemPivot whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemPivot whereProductQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemPivot whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemPivot whereSubTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemPivot whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItemPivot whereVariationId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'product_id',
    'variation_id',
    'product_quantity',
    'price',
    'sub_total',
    'status',
])]
#[Table(name: 'order_items', key: 'id')]
class OrderItemPivot extends Pivot
{
    public $incrementing = true;

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'sub_total' => 'float',
            'product_quantity' => 'integer',
            'variation_id' => 'integer',
            'order_id' => 'integer',
            'product_id' => 'integer',
            'id' => 'integer',
        ];
    }
}
