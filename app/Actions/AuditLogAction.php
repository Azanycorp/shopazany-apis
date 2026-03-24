<?php

namespace App\Actions;

use App\DTO\AuditLog;
use App\Services\AuditLog\AuditLogger;

class AuditLogAction
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {}

    public function execute(AuditLog $auditLog): void
    {
        $this->audit
            ->actor($auditLog->user)
            ->on($auditLog->model)
            ->old(
                is_array($auditLog->before)
                    ? $auditLog->before
                    : optional($auditLog->before)->getAttributes() ?? []
            )
            ->new($auditLog->model?->getAttributes())
            ->describe($auditLog->description)
            ->tag($auditLog->tags)
            ->meta(['source' => 'api', 'ip' => request()->ip()])
            ->log($auditLog->event);
    }
}
