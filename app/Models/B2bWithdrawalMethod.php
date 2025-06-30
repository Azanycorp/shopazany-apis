<?php

namespace App\Models;

use App\Enum\UserType;
use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;

class B2bWithdrawalMethod extends Model
{
    use ClearsResponseCache;

    protected $fillable = [
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
    ];

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
