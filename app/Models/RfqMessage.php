<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqMessage extends Model
{
    protected $fillable = [
        'rfq_id',
        'buyer_id',
        'seller_id',
        'seller_unit_price',
        'p_unit_price',
        'preferred_qty',
        'note',
    ];

    public function buyer(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'seller_id');
    }

    public function rfq(): BelongsTo
    {
        return $this->BelongsTo(Rfq::class, 'rfq_id');
    }
}
