<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action_id',
        'points',
        'is_rewarded',
        'status'
    ];

    public function user(): BelongsTo
    {
    	return $this->belongsTo(User::class, 'user_id');
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class, 'action_id');
    }
}
