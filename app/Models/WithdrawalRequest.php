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
 * @property string|null $user_type
 * @property numeric $amount
 * @property numeric $previous_balance
 * @property numeric $current_balance
 * @property string $status
 * @property string|null $reference
 * @property string|null $transfer_code
 * @property array<array-key, mixed>|null $response
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WithdrawalRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WithdrawalRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WithdrawalRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WithdrawalRequest whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WithdrawalRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WithdrawalRequest whereCurrentBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WithdrawalRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WithdrawalRequest wherePreviousBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WithdrawalRequest whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WithdrawalRequest whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WithdrawalRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WithdrawalRequest whereTransferCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WithdrawalRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WithdrawalRequest whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WithdrawalRequest whereUserType($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'user_type',
    'amount',
    'previous_balance',
    'current_balance',
    'status',
    'reference',
    'response',
    'transfer_code',
])]
class WithdrawalRequest extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'response' => 'array',
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
