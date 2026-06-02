<?php

namespace App\Models;

use App\Enum\AuditEvent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string|null $actor_type
 * @property int|null $actor_id
 * @property AuditEvent $event
 * @property string|null $description
 * @property string|null $auditable_type
 * @property int|null $auditable_id
 * @property array<array-key, mixed>|null $old_values
 * @property array<array-key, mixed>|null $new_values
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $url
 * @property string|null $method
 * @property array<array-key, mixed>|null $tags
 * @property array<array-key, mixed>|null $metadata
 * @property Carbon $created_at
 * @property-read Model|\Eloquent|null $actor
 * @property-read Model|\Eloquent|null $auditable
 *
 * @method static Builder<static>|AuditLog critical()
 * @method static Builder<static>|AuditLog forActor(\Illuminate\Database\Eloquent\Model $actor)
 * @method static Builder<static>|AuditLog forAuditable(\Illuminate\Database\Eloquent\Model $model)
 * @method static Builder<static>|AuditLog forEvent(\App\Enum\AuditEvent|string $event)
 * @method static Builder<static>|AuditLog inDateRange(string $from, string $to)
 * @method static Builder<static>|AuditLog newModelQuery()
 * @method static Builder<static>|AuditLog newQuery()
 * @method static Builder<static>|AuditLog query()
 * @method static Builder<static>|AuditLog whereActorId($value)
 * @method static Builder<static>|AuditLog whereActorType($value)
 * @method static Builder<static>|AuditLog whereAuditableId($value)
 * @method static Builder<static>|AuditLog whereAuditableType($value)
 * @method static Builder<static>|AuditLog whereCreatedAt($value)
 * @method static Builder<static>|AuditLog whereDescription($value)
 * @method static Builder<static>|AuditLog whereEvent($value)
 * @method static Builder<static>|AuditLog whereId($value)
 * @method static Builder<static>|AuditLog whereIpAddress($value)
 * @method static Builder<static>|AuditLog whereMetadata($value)
 * @method static Builder<static>|AuditLog whereMethod($value)
 * @method static Builder<static>|AuditLog whereNewValues($value)
 * @method static Builder<static>|AuditLog whereOldValues($value)
 * @method static Builder<static>|AuditLog whereTags($value)
 * @method static Builder<static>|AuditLog whereUrl($value)
 * @method static Builder<static>|AuditLog whereUserAgent($value)
 * @method static Builder<static>|AuditLog withTag(string $tag)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'actor_type',
    'actor_id',
    'event',
    'description',
    'auditable_type',
    'auditable_id',
    'old_values',
    'new_values',
    'ip_address',
    'user_agent',
    'url',
    'method',
    'tags',
    'metadata',
    'created_at',
])]
#[WithoutTimestamps]
class AuditLog extends Model
{
    protected function casts(): array
    {
        return [
            'event' => AuditEvent::class,
            'old_values' => 'array',
            'new_values' => 'array',
            'tags' => 'array',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    #[Scope]
    protected function forEvent(Builder $query, AuditEvent|string $event): Builder
    {
        $value = $event instanceof AuditEvent ? $event->value : $event;

        return $query->where('event', $value);
    }

    #[Scope]
    protected function forActor(Builder $query, Model $actor): Builder
    {
        return $query->where('actor_type', $actor->getMorphClass())
            ->where('actor_id', $actor->getKey());
    }

    #[Scope]
    protected function forAuditable(Builder $query, Model $model): Builder
    {
        return $query->where('auditable_type', $model->getMorphClass())
            ->where('auditable_id', $model->getKey());
    }

    #[Scope]
    protected function withTag(Builder $query, string $tag): Builder
    {
        return $query->whereJsonContains('tags', $tag);
    }

    #[Scope]
    protected function critical(Builder $query): Builder
    {
        $criticalEvents = $criticalEvents = (new Collection(AuditEvent::cases()))
            ->filter(fn ($e) => $e->isCritical())
            ->map(fn ($e) => $e->value)
            ->all();

        return $query->whereIn('event', $criticalEvents);
    }

    #[Scope]
    protected function inDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function getChangedAttributes(): array
    {
        $old = is_array($this->old_values) ? $this->old_values : [];
        $new = is_array($this->new_values) ? $this->new_values : [];

        return array_keys(array_diff_assoc($new, $old));
    }
}
