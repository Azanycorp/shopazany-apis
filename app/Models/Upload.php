<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload withoutTrashed()
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'file_original_name', 'file_name', 'user_id', 'extension', 'type', 'file_size',
])]
#[Table(name: 'uploads')]
class Upload extends Model
{
    use HasFactory, SoftDeletes;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
