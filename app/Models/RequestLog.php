<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $method
 * @property string $url
 * @property string|null $route
 * @property array<array-key, mixed>|null $headers
 * @property array<array-key, mixed>|null $payload
 * @property array<array-key, mixed>|null $response
 * @property int|null $status_code
 * @property string|null $ip_address
 * @property string $user_agent
 * @property int|null $user_id
 * @property float|null $duration_ms
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLog whereDurationMs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLog whereHeaders($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLog whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLog wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLog whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLog whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLog whereStatusCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLog whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLog whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'method',
    'url',
    'route',
    'headers',
    'payload',
    'response',
    'status_code',
    'ip_address',
    'user_agent',
    'user_id',
    'duration_ms',
])]
class RequestLog extends Model
{
    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'payload' => 'array',
            'response' => 'array',
        ];
    }
}
