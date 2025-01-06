<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payout extends Model
{
    protected $fillable = [
        'seller_id',
        'amount',
        'fee',
        'account_name',
        'account_number',
        'bank',
        'status',
        'date_paid',
    ];

    function paymentInfo(): HasOne
    {
        return $this->hasOne(BankAccount::class, 'user_id', 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'seller_id', 'id')->where([
            'type' => 'b2b_seller'
        ]);
    }

    public static function stats()
    {
        return DB::select(
            "SELECT
                (SELECT ROUND(SUM(`amount`), 2) FROM `payouts` WHERE `status`='paid') AS total_payout,
              -- Today

                (SELECT ROUND(SUM(`amount`), 2)
                    FROM `payouts`
                    WHERE `status`='paid' AND
                    DAY(created_at) = DAY(NOW())
                ) AS total_payout_today,

-- Weekly

                (SELECT ROUND(SUM(`amount`), 2)
                    FROM `payouts`
                    WHERE `status`='paid' AND
                    WEEK(created_at) = WEEK(NOW())
                ) AS total_payout_this_week,

       -- Monthly

                (SELECT
                    ROUND(SUM(`amount`), 2)
                    FROM `payouts`
                    WHERE `status`='paid' AND
                        MONTH(created_at) = MONTH(NOW()) AND
                        YEAR(created_at) = YEAR(NOW())
                ) AS total_payout_this_month,

    -- Yearly

                (SELECT
                    ROUND(SUM(`amount`), 2)
                    FROM `payouts`
                    WHERE `status`='paid' AND
                    YEAR(created_at) = YEAR(NOW())
                ) AS total_paid_this_year

                 "
        )[0];
    }
}
