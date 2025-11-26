<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Rfq extends Model
{
    protected $fillable = [
        'buyer_id',
        'seller_id',
        'quote_no',
        'product_id',
        'product_quantity',
        'p_unit_price',
        'product_data',
        'total_amount',
        'payment_status',
        'status',
        'type',
        'delivery_date',
        'shipped_date',
    ];

    protected function casts(): array
    {
        return [
            'product_data' => 'array',
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, $this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, $this>
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id', 'id');
    }

    public function messages(): HasMany
    {
        return $this->HasMany(RfqMessage::class);
    }

    public static function stats()
    {
        return DB::select("SELECT
                (SELECT ROUND(SUM(`total_amount`), 2) FROM `rfqs` WHERE `status`='confirmed') AS total_sales,
                -- Today

                (SELECT ROUND(SUM(`total_amount`), 2)
                    FROM `rfqs`
                    WHERE `status`='confirmed' AND
                    DAY(created_at) = DAY(NOW())
                ) AS total_sales_today,

                -- Weekly

                (SELECT ROUND(SUM(`total_amount`), 2)
                    FROM `rfqs`
                    WHERE `status`='confirmed' AND
                    WEEK(created_at) = WEEK(NOW())
                ) AS total_sales_this_week,

                -- Monthly

                (SELECT
                    ROUND(SUM(`total_amount`), 2)
                    FROM `rfqs`
                    WHERE `status`='confirmed' AND
                        MONTH(created_at) = MONTH(NOW()) AND
                        YEAR(created_at) = YEAR(NOW())
                ) AS total_sales_this_month,

                -- Yearly

                (SELECT
                    ROUND(SUM(`total_amount`), 2)
                    FROM `rfqs`
                    WHERE `status`='confirmed' AND
                    YEAR(created_at) = YEAR(NOW())
                ) AS total_sales_this_year

                 ")[0];
    }
}
