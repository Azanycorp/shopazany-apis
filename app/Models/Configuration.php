<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    protected $fillable = [
        'usd_rate',
        'company_profit',
        'email_verify',
        'currency_code',
        'currency_symbol',
        'promotion_start_date',
        'promotion_end_date',
        'promo_type',
        'jolly_promo',
        'min_deposit',
        'max_deposit',
        'min_withdrawal',
        'max_withdrawal',
        'withdrawal_fee',
        'seller_perc',
        'paystack_perc',
        'paystack_fixed',
    ];
}
