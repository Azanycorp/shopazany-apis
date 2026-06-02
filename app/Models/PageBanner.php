<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $page
 * @property string|null $type
 * @property string|null $section
 * @property string|null $banner_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageBanner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageBanner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageBanner query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageBanner whereBannerUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageBanner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageBanner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageBanner wherePage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageBanner whereSection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageBanner whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageBanner whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'page',
    'section',
    'type',
    'banner_url',
])]
class PageBanner extends Model {}
