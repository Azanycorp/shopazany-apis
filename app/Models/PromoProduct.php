<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $promo_id
 * @property int $product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoProduct wherePromoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoProduct whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'promo_id',
    'product_id',
])]
#[Table(name: 'promo_products')]
class PromoProduct extends Model
{
    use HasFactory;
}
