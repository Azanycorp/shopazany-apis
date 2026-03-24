<?php

namespace App\Services\AuditLog;

use App\Enum\AuditEvent;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;

final class AuditLogger
{
    private ?Model $actor = null;

    private ?Model $auditable = null;

    private ?array $oldValues = null;

    private ?array $newValues = null;

    private ?string $description = null;

    private array $tags = [];

    private array $metadata = [];

    private bool $withContext = true;

    /**
     * Set who performed the action.
     * Defaults to the currently authenticated user if not called.
     */
    public function actor(Model $actor): static
    {
        $this->actor = $actor;

        return $this;
    }

    /** Set the model being acted upon */
    public function on(Model $model): static
    {
        $this->auditable = $model;

        return $this;
    }

    /** Provide the before-state of the auditable model */
    public function old(array $values): static
    {
        $this->oldValues = $values;

        return $this;
    }

    /** Provide the after-state of the auditable model */
    public function new(array $values): static
    {
        $this->newValues = $values;

        return $this;
    }

    /**
     * Capture only the dirty attributes from an Eloquent model.
     * Pass the model BEFORE save() for $original, and AFTER save() for $current.
     */
    public function diff(Model $model): static
    {
        $this->oldValues = array_intersect_key(
            $model->getOriginal(),
            $model->getDirty()
        );
        $this->newValues = $model->getDirty();

        return $this;
    }

    /** Add a human-readable summary */
    public function describe(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /** Add one or more tags for filtering */
    public function tag(string ...$tags): static
    {
        $this->tags = array_merge($this->tags, $tags);

        return $this;
    }

    /** Attach arbitrary key-value metadata */
    public function meta(array $metadata): static
    {
        $this->metadata = array_merge($this->metadata, $metadata);

        return $this;
    }

    /** Skip auto-capturing request context (IP, URL, user agent) */
    public function withoutContext(): static
    {
        $this->withContext = false;

        return $this;
    }

    public function log(AuditEvent|string $event): AuditLog
    {
        $eventValue = $event instanceof AuditEvent ? $event->value : $event;

        $actor = $this->actor ?? Auth::user();

        $data = [
            'event' => $eventValue,
            'description' => $this->description,
            'actor_type' => $actor?->getMorphClass(),
            'actor_id' => $actor?->getKey(),
            'auditable_type' => $this->auditable?->getMorphClass(),
            'auditable_id' => $this->auditable?->getKey(),
            'old_values' => $this->oldValues,
            'new_values' => $this->newValues,
            'tags' => $this->tags ?: null,
            'metadata' => $this->metadata ?: null,
        ];

        if ($this->withContext) {
            $request = RequestFacade::instance();
            $data['ip_address'] = $request->ip();
            $data['user_agent'] = $request->userAgent();
            $data['url'] = $request->fullUrl();
            $data['method'] = $request->method();
        }

        $log = AuditLog::create($data);

        $this->reset();

        return $log;
    }

    /**
     * Quick one-liner for simple events.
     *
     * Usage:
     *   AuditLogger::record(AuditEvent::LoggedIn, $user);
     *   AuditLogger::record('custom_event', actor: $user, description: 'Did something');
     */
    public static function record(
        AuditEvent|string $event,
        ?Model $actor = null,
        ?Model $auditable = null,
        ?string $description = null,
        array $tags = [],
        array $metadata = [],
    ): AuditLog {
        $logger = new static;

        if ($actor) {
            $logger->actor($actor);
        }

        if ($auditable) {
            $logger->on($auditable);
        }

        if ($description) {
            $logger->describe($description);
        }

        if ($tags) {
            $logger->tag(...$tags);
        }

        if ($metadata) {
            $logger->meta($metadata);
        }

        return $logger->log($event);
    }

    private function reset(): void
    {
        $this->actor = null;
        $this->auditable = null;
        $this->oldValues = null;
        $this->newValues = null;
        $this->description = null;
        $this->tags = [];
        $this->metadata = [];
        $this->withContext = true;
    }
}
