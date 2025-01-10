<?php

namespace App\Models;

use App\Enum\UserType;
use Illuminate\Database\Eloquent\Model;

class B2bWithdrawalMethod extends Model
{
    protected $fillable = [
        'country_id',
        'user_id',
        'account_name',
        'account_number',
        'account_type',
        'bank_name',
        'routing_number',
        'bic_swift_code',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id')->where('type',UserType::B2B_BUYER)->withDefault([
            'name'=> 'guest'
        ]);
    }
}
