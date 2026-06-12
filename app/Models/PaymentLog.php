<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $payment_id
 * @property array<array-key, mixed> $data
 * @property string $method
 * @property string $status
 * @property string|null $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentLog whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'payment_id',
    'data',
    'method',
    'status',
    'type',
])]
class PaymentLog extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }
}
