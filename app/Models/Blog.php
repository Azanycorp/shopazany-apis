<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $admin_id
 * @property string|null $type
 * @property string $title
 * @property string $slug
 * @property string $image
 * @property string|null $public_id
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Admin|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog wherePublicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'admin_id',
    'title',
    'type',
    'image',
    'public_id',
    'slug',
    'description',
])]
class Blog extends Model
{
    /**
     * @return BelongsTo<Admin, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
