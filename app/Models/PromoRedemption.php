<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $user_id
 * @property int $promo_id
 * @property int|null $product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $id
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoRedemption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoRedemption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoRedemption query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoRedemption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoRedemption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoRedemption whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoRedemption wherePromoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoRedemption whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoRedemption whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['user_id', 'promo_id', 'product_id'])]
class PromoRedemption extends Model {}
