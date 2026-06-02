<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $heading_one
 * @property string $sub_text_one
 * @property string $heading_two
 * @property string $sub_text_two
 * @property string $image_one
 * @property string $image_two
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereHeadingOne($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereHeadingTwo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereImageOne($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereImageTwo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereSubTextOne($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereSubTextTwo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AboutUs whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'heading_one',
    'sub_text_one',
    'heading_two',
    'sub_text_two',
    'image_one',
    'image_two',
])]
class AboutUs extends Model
{
    use HasFactory;
}
