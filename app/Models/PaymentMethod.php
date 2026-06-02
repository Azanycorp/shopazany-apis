<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string|null $platform
 * @property string|null $bank_name
 * @property string|null $account_number
 * @property string|null $account_name
 * @property string|null $routing_number
 * @property string|null $recipient_code
 * @property array<array-key, mixed>|null $data
 * @property bool $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereRecipientCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereRoutingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentMethod whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'type',
    'bank_name',
    'account_number',
    'account_name',
    'recipient_code',
    'data',
    'platform',
    'routing_number',
    'is_default',
])]
class PaymentMethod extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_default' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
