<?php

namespace App\Models;

use App\Trait\ClearsResponseCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $title
 * @property string|null $slug
 * @property string $image
 * @property string|null $public_id
 * @property string $start_date
 * @property string $end_date
 * @property array<array-key, mixed> $products
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $type
 * @property int|null $deal_id
 * @property-read mixed $b2b_products
 * @property-read Deal|null $deal
 * @property-read mixed $product_ids
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereDealId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner wherePublicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'title',
    'slug',
    'image',
    'public_id',
    'start_date',
    'end_date',
    'type',
    'products',
    'status',
    'deal_id',
])]
class Banner extends Model
{
    use ClearsResponseCache, HasFactory;

    protected function casts(): array
    {
        return [
            'products' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Deal, $this>
     */
    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }

    protected function products(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Product::whereIn('id', is_array($value) ? $value : json_decode($value, true))
                ->select(['id', 'name', 'product_price', 'description', 'discount_price', 'slug'])
                ->get()
        );
    }

    protected function productIds(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => is_array($attributes['products'])
                ? $attributes['products']
                : json_decode($attributes['products'], true)
        );
    }

    protected function b2bProducts(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                $ids = json_decode($attributes['products'] ?? '[]', true);

                if (blank($ids)) {
                    return new Collection([]);
                }

                return B2BProduct::whereIn('id', $ids)->get();
            }
        );
    }
}
