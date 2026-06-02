<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property float $usd_rate Local currency rate per USD
 * @property int $company_profit Current month profit
 * @property string|null $email_verify
 * @property string|null $currency_code eg: USD
 * @property string|null $currency_symbol eg: $
 * @property string|null $promotion_start_date
 * @property string|null $promotion_end_date
 * @property string|null $promo_type
 * @property string|null $jolly_promo
 * @property float $min_deposit
 * @property float $max_deposit
 * @property float $min_withdrawal
 * @property float $max_withdrawal
 * @property float $withdrawal_fee In %
 * @property float $seller_perc In %
 * @property int $paystack_perc Paystack charges percentage
 * @property float $paystack_fixed Paystack fixed prize for charges
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $withdrawal_frequency
 * @property string $withdrawal_status
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration whereCompanyProfit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration whereCurrencySymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration whereEmailVerify($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration whereJollyPromo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration whereMaxDeposit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration whereMaxWithdrawal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration whereMinDeposit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration whereMinWithdrawal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration wherePaystackFixed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration wherePaystackPerc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration wherePromoType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration wherePromotionEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration wherePromotionStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration whereSellerPerc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration whereUsdRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration whereWithdrawalFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration whereWithdrawalFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Configuration whereWithdrawalStatus($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
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
    'withdrawal_frequency',
    'withdrawal_status',
    'withdrawal_fee',
    'seller_perc',
    'paystack_perc',
    'paystack_fixed',
])]
class Configuration extends Model {}
