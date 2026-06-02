<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $batch_id
 * @property string $comment
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BatchActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BatchActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BatchActivity query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BatchActivity whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BatchActivity whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BatchActivity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BatchActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BatchActivity whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BatchActivity whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'batch_id',
    'comment',
    'note',
])]
#[Hidden([
    'updated_at',
])]
class BatchActivity extends Model {}
