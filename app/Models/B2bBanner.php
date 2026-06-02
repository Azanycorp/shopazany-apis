<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $image
 * @property string $start_date
 * @property string $end_date
 * @property array<array-key, mixed> $products
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bBanner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bBanner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bBanner query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bBanner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bBanner whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bBanner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bBanner whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bBanner whereProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bBanner whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bBanner whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bBanner whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2bBanner whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'title',
    'image',
    'start_date',
    'end_date',
    'products',
    'status',
])]
class B2bBanner extends Model
{
    use ClearsResponseCache;

    protected function casts(): array
    {
        return [
            'products' => 'array',
        ];
    }
}
