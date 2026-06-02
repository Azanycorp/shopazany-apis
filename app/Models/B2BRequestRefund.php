<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $b2b_product_id
 * @property string $complaint_number
 * @property string $order_number
 * @property string $type
 * @property string $additional_note
 * @property bool $send_reply
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read B2BProduct|null $b2bProduct
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BRequestRefund newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BRequestRefund newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BRequestRefund query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BRequestRefund whereAdditionalNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BRequestRefund whereB2bProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BRequestRefund whereComplaintNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BRequestRefund whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BRequestRefund whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BRequestRefund whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BRequestRefund whereSendReply($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BRequestRefund whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BRequestRefund whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BRequestRefund whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BRequestRefund whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'b2b_product_id',
    'complaint_number',
    'order_number',
    'type',
    'additional_note',
    'send_reply',
    'status',
])]
class B2BRequestRefund extends Model
{
    protected function casts(): array
    {
        return [
            'send_reply' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<B2BProduct, $this>
     */
    public function b2bProduct(): BelongsTo
    {
        return $this->belongsTo(B2BProduct::class, 'b2b_product_id');
    }
}
