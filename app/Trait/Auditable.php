<?php

namespace App\Trait;

use App\Models\AuditLog;
use App\Observers\AuditableObserver;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::observe(AuditableObserver::class);
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')
            ->latest('created_at');
    }

    /**
     * Attributes that should NEVER appear in audit logs (passwords, tokens, etc.)
     * Override in your model to customise.
     */
    public function auditExclude(): array
    {
        return ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];
    }

    /**
     * Only audit changes to these specific attributes.
     * Return an empty array (default) to audit ALL attributes (minus excluded ones).
     */
    public function auditOnly(): array
    {
        return [];
    }

    /**
     * Return false to disable auditing for this model entirely.
     * Useful for toggling off in tests or bulk operations.
     */
    public function shouldAudit(): bool
    {
        return true;
    }

    /**
     * Tags to attach to every audit log entry for this model.
     * Override to add model-specific tags.
     */
    public function auditTags(): array
    {
        return [];
    }

    /**
     * Sanitise an attribute array: strip excluded keys, optionally whitelist.
     * Called by the observer before writing old/new values.
     *
     * @internal
     */
    public function prepareAuditValues(array $values): array
    {
        $exclude = $this->auditExclude();
        $only = $this->auditOnly();

        $filtered = array_diff_key($values, array_flip($exclude));

        if (filled($only)) {
            $filtered = array_intersect_key($filtered, array_flip($only));
        }

        return $filtered;
    }

    /**
     * Temporarily disable auditing for a bulk operation.
     *
     * Usage:
     *   User::withoutAudit(fn () => User::query()->update(['status' => 'active']));
     */
    public static function withoutAudit(callable $callback): mixed
    {
        AuditableObserver::mute();
        $result = $callback();
        AuditableObserver::unmute();

        return $result;
    }
}
