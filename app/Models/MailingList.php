<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailingList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailingList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailingList query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailingList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailingList whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailingList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailingList whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailingList whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['user_id', 'email'])]
class MailingList extends Model
{
    use HasFactory;
}
