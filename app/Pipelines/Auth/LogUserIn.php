<?php

namespace App\Pipelines\Auth;

use App\Actions\AuditLogAction;
use App\Trait\Login;

class LogUserIn
{
    use Login;

    public function __construct(
        private AuditLogAction $auditLogAction
    ) {}

    public function handle($request)
    {
        return $this->logUserIn($request->user, $request, $this->auditLogAction);
    }
}
