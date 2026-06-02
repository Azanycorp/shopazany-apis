<?php

namespace App\Models;

use App\Enum\UserType;
use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $account_name
 * @property string|null $account_number
 * @property string|null $bank_name
 * @property string|null $routing_number
 * @property string|null $bic_swift_code
 * @property int $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $status
 * @property string|null $admin_comment
 * @property string|null $platform
 * @property string|null $recipient
 * @property string|null $reference
 * @property string|null $recipient_code
 * @property string|null $type
 * @property string|null $paypal_email
 * @property string|null $data
 * @property-read Country|null $country
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod whereAdminComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod whereBicSwiftCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod wherePaypalEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod whereRecipient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod whereRecipientCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod whereRoutingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bWithdrawalMethod whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'user_id',
    'account_name',
    'account_number',
    'data',
    'type',
    'paypal_email',
    'bank_name',
    'is_default',
    'platform',
    'recipient',
    'routing_number',
    'reference',
    'recipient_code',
])]
class B2bWithdrawalMethod extends Model
{
    use ClearsResponseCache;

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->where('type', UserType::B2B_BUYER)->withDefault([
            'name' => 'guest',
        ]);
    }
}
