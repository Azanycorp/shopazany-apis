<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $rfq_id
 * @property float $p_unit_price
 * @property string|null $note
 * @property float $preferred_qty
 * @property int|null $seller_id
 * @property int|null $buyer_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property numeric $seller_unit_price
 * @property-read User|null $buyer
 * @property-read Rfq|null $rfq
 * @property-read User|null $seller
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqMessage whereBuyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqMessage whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqMessage wherePUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqMessage wherePreferredQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqMessage whereRfqId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqMessage whereSellerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqMessage whereSellerUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqMessage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'rfq_id',
    'buyer_id',
    'seller_id',
    'seller_unit_price',
    'p_unit_price',
    'preferred_qty',
    'note',
])]
class RfqMessage extends Model
{
    public function buyer(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'seller_id');
    }

    public function rfq(): BelongsTo
    {
        return $this->BelongsTo(Rfq::class, 'rfq_id');
    }
}
