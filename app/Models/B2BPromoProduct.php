<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $b2b_promo_id
 * @property int $product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoProduct whereB2bPromoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|B2BPromoProduct whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'promo_id',
    'product_id',
])]
#[Table(name: 'b2b_promo_products')]
class B2BPromoProduct extends Model {}
