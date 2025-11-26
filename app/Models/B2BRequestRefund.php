<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class B2BRequestRefund extends Model
{
    protected $fillable = [
        'user_id',
        'b2b_product_id',
        'complaint_number',
        'order_number',
        'type',
        'additional_note',
        'send_reply',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'send_reply' => 'boolean',
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\B2BProduct, $this>
     */
    public function b2bProduct(): BelongsTo
    {
        return $this->belongsTo(B2BProduct::class, 'b2b_product_id');
    }
}
