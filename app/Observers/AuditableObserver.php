<?php

namespace App\Observers;

use App\Contracts\Auditable as AuditableContract;
use App\Enum\AuditEvent;
use App\Services\AuditLog\AuditLogger;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    protected static bool $muted = false;

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

    /**
     * @param  Model&AuditableContract  $model
     */
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

    /**
     * @param  Model&AuditableContract  $model
     */
    public function updated(Model $model): void
    {
        if (! $this->shouldLog($model)) {
            return;
        }

        $dirty = $model->getDirty();
        if (blank($dirty)) {
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

    /**
     * @param  Model&AuditableContract  $model
     */
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

    /**
     * @param  Model&AuditableContract  $model
     */
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

        if (! $model instanceof AuditableContract) {
            return false;
        }

        if (! $model->shouldAudit()) {
            return false;
        }

        return true;
    }
}
