<?php

namespace App\Actions;

use App\DTO\AuditLog;
use App\Services\AuditLog\AuditLogger;
use Illuminate\Http\Request;

class AuditLogAction
{
    public function __construct(
        private readonly AuditLogger $audit,
    ) {}

    public function execute(AuditLog $auditLog, Request $request): void
    {
        $this->audit
            ->actor($auditLog->user)
            ->on($auditLog->model)
            ->old(
                is_array($auditLog->before)
                    ? $auditLog->before
                    : $auditLog->before?->getAttributes() ?? []
            )
            ->new($auditLog->model?->getAttributes())
            ->describe($auditLog->description)
            ->tag($auditLog->tags)
            ->meta(['source' => 'api', 'ip' => $request->ip()])
            ->log($auditLog->event);
    }
}
