<?php

namespace App\Pipelines\Auth;

use App\Actions\AuditLogAction;
use App\Trait\Login;
use Closure;

class HandleTwoFactor
{
    use Login;

    public function __construct(
        private AuditLogAction $auditLogAction
    ) {}

    public function handle($request, Closure $next)
    {
        if ($request->user->two_factor_enabled) {
            return $this->handleTwoFactorAuthentication($request->user, $request, $this->auditLogAction);
        }

        return $next($request);
    }
}
