<?php

namespace App\Models;

use App\Enum\AuditEvent;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

/**
 * @property array|null $old_values
 * @property array|null $new_values
 */
class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
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
    ];

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
