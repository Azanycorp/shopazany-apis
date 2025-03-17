<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderActivity extends Model
{
    protected $fillable = ['order_id', 'message', 'status', 'date'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
