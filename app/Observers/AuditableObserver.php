<?php

namespace App\Observers;

use App\Enum\AuditEvent;
use App\Services\AuditLog\AuditLogger;
use App\Trait\Auditable;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    private static bool $muted = false;

    public static function mute(): void
    {
        static::$muted = true;
    }

    public static function unmute(): void
    {
        static::$muted = false;
    }

    public static function isMuted(): bool
    {
        return static::$muted;
    }

    public function created(Model $model): void
    {
        if (! $this->shouldLog($model)) {
            return;
        }

        (new AuditLogger)
            ->on($model)
            ->new($model->prepareAuditValues($model->getAttributes()))
            ->tag(...$model->auditTags())
            ->log(AuditEvent::Created);
    }

    public function updated(Model $model): void
    {
        if (! $this->shouldLog($model)) {
            return;
        }

        $dirty = $model->getDirty();
        if (empty($dirty)) {
            return;
        }

        $oldValues = array_intersect_key($model->getOriginal(), $dirty);
        $newValues = $dirty;

        (new AuditLogger)
            ->on($model)
            ->old($model->prepareAuditValues($oldValues))
            ->new($model->prepareAuditValues($newValues))
            ->tag(...$model->auditTags())
            ->log(AuditEvent::Updated);
    }

    public function deleted(Model $model): void
    {
        if (! $this->shouldLog($model)) {
            return;
        }

        // Distinguish soft-delete from a hard delete
        $event = method_exists($model, 'isForceDeleting') && $model->isForceDeleting()
            ? AuditEvent::ForceDeleted
            : AuditEvent::Deleted;

        (new AuditLogger)
            ->on($model)
            ->old($model->prepareAuditValues($model->getAttributes()))
            ->tag(...$model->auditTags())
            ->log($event);
    }

    public function restored(Model $model): void
    {
        if (! $this->shouldLog($model)) {
            return;
        }

        (new AuditLogger)
            ->on($model)
            ->tag(...$model->auditTags())
            ->log(AuditEvent::Restored);
    }

    private function shouldLog(Model $model): bool
    {
        if (static::$muted) {
            return false;
        }

        if (! in_array(Auditable::class, class_uses_recursive($model))) {
            return false;
        }

        if (! $model->shouldAudit()) {
            return false;
        }

        return true;
    }
}
