<?php

namespace App\Models;

use App\Enum\AuditEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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

    public function scopeForEvent(Builder $query, AuditEvent|string $event): Builder
    {
        $value = $event instanceof AuditEvent ? $event->value : $event;

        return $query->where('event', $value);
    }

    public function scopeForActor(Builder $query, Model $actor): Builder
    {
        return $query->where('actor_type', $actor->getMorphClass())
            ->where('actor_id', $actor->getKey());
    }

    public function scopeForAuditable(Builder $query, Model $model): Builder
    {
        return $query->where('auditable_type', $model->getMorphClass())
            ->where('auditable_id', $model->getKey());
    }

    public function scopeWithTag(Builder $query, string $tag): Builder
    {
        return $query->whereJsonContains('tags', $tag);
    }

    public function scopeCritical(Builder $query): Builder
    {
        $criticalEvents = collect(AuditEvent::cases())
            ->filter(fn ($e) => $e->isCritical())
            ->map(fn ($e) => $e->value)
            ->all();

        return $query->whereIn('event', $criticalEvents);
    }

    public function scopeInDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function getChangedAttributes(): array
    {
        if (! $this->old_values || ! $this->new_values) {
            return [];
        }

        return array_keys(
            array_diff_assoc($this->new_values, $this->old_values)
        );
    }
}
