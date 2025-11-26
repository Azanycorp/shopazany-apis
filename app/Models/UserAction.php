<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property float|null $value
 * @property string|null $currency
 */
class UserAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action_id',
        'points',
        'is_rewarded',
        'status',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Action, $this>
     */
    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class, 'action_id');
    }
}
