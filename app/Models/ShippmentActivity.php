<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $shippment_id
 * @property string $comment
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentActivity query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentActivity whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentActivity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentActivity whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentActivity whereShippmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShippmentActivity whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'shippment_id',
    'comment',
    'note',
])]
#[Hidden([
    'updated_at',
])]
class ShippmentActivity extends Model {}
