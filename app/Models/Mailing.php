<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $type
 * @property string $email
 * @property string|null $subject
 * @property string|null $body
 * @property string $mailable
 * @property array<array-key, mixed> $payload
 * @property string $status
 * @property int $attempts
 * @property int $max_attempts
 * @property array<array-key, mixed>|null $error_response
 * @property string|null $scheduled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mailing newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mailing newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mailing query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mailing whereAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mailing whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mailing whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mailing whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mailing whereErrorResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mailing whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mailing whereMailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mailing whereMaxAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mailing wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mailing whereScheduledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mailing whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mailing whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mailing whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mailing whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'type',
    'email',
    'subject',
    'body',
    'mailable',
    'status',
    'attempts',
    'max_attempts',
    'scheduled_at',
    'error_response',
])]
class Mailing extends Model
{
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'error_response' => 'array',
        ];
    }

    public static function saveData(array $data): self
    {
        $mail = new self;
        $mail->type = $data['type'];
        $mail->email = $data['email'];
        $mail->subject = $data['subject'];
        $mail->body = $data['body'];
        $mail->mailable = $data['mailable'];
        $mail->payload = $data['payload'];

        $mail->save();

        return $mail;
    }
}
