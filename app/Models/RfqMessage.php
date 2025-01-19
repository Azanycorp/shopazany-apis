<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqMessage extends Model
{
    protected $fillable = [
        'rfq_id',
        'p_unit_price',
        'preferred_qty',
        'note',
    ];

    public function rfq(): BelongsTo
    {
        return $this->BelongsTo(RfqMessage::class,'rfq_id');
    }
}
