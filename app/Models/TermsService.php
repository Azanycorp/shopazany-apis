<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $title
 * @property string|null $slug
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TermsService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TermsService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TermsService query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TermsService whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TermsService whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TermsService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TermsService whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TermsService whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TermsService whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'title',
    'slug',
    'description',
])]
class TermsService extends Model
{
    use HasFactory;
}
