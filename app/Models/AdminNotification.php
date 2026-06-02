<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $content
 * @property bool $is_read
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminNotification whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['title', 'content', 'is_read'])]
class AdminNotification extends Model
{
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }
}
