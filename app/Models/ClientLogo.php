<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $logo
 * @property string|null $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLogo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLogo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLogo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLogo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLogo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLogo whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLogo whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLogo whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientLogo whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['name', 'logo', 'type'])]
class ClientLogo extends Model {}
