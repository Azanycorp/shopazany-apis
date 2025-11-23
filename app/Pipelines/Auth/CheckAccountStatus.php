<?php

namespace App\Pipelines\Auth;

use App\Enum\UserLog;
use App\Enum\UserStatus;
use App\Trait\Login;
use Closure;

class CheckAccountStatus
{
    use Login;

    public function handle($request, Closure $next)
    {
        $user = $request->user;

        if ($this->isAccountUnverifiedOrInactive($user)) {
            return $this->handleAccountIssues($user, $request, 'Account not verified or inactive', UserLog::LOGIN_ATTEMPT);
        }

        if ($this->isAccountPending($user)) {
            return $this->handleAccountIssues($user, $request, 'Account pending', UserLog::LOGIN_ATTEMPT, UserStatus::PENDING);
        }

        if ($this->isAccountSuspended($user)) {
            return $this->handleAccountIssues($user, $request, 'Account suspended', UserLog::LOGIN_ATTEMPT, UserStatus::SUSPENDED);
        }

        if ($this->isAccountBlocked($user)) {
            return $this->handleAccountIssues($user, $request, 'Account blocked', UserLog::LOGIN_ATTEMPT, UserStatus::BLOCKED);
        }

        if (! $user->is_admin_approve) {
            return $this->handleAccountIssues($user, $request, 'Account not approved', UserLog::LOGIN_ATTEMPT, UserStatus::BLOCKED);
        }

        return $next($request);
    }
}
