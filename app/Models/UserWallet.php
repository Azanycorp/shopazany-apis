<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $seller_id b2b seller wallet
 * @property float $master_wallet total available earnings
 * @property float $transaction_wallet
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserWallet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserWallet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserWallet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserWallet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserWallet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserWallet whereMasterWallet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserWallet whereSellerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserWallet whereTransactionWallet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserWallet whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'seller_id',
    'master_wallet',
    'transaction_wallet',
])]
class UserWallet extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'seller_id', 'id')->where([
            'type' => 'b2b_seller',
        ]);
    }
}
