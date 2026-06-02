<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $seller_id b2b seller transaction history
 * @property string|null $payment_id Funding ref ID
 * @property string $type
 * @property float $credit
 * @property float $debit
 * @property string|null $remark
 * @property string|null $funding_pop
 * @property string|null $status handles funding
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionWallet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionWallet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionWallet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionWallet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionWallet whereCredit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionWallet whereDebit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionWallet whereFundingPop($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionWallet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionWallet wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionWallet whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionWallet whereSellerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionWallet whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionWallet whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransactionWallet whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'payment_id',
    'type',
    'credit',
    'debit',
    'remark',
    'funding_pop',
    'status',
])]
class TransactionWallet extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->where([
            'type' => 'b2b_seller',
        ]);
    }
}
