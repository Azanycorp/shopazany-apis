<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $symbol
 * @property string $format
 * @property string $exchange_rate
 * @property int $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Table(name: 'currencies')]
class Currency extends Model {}
