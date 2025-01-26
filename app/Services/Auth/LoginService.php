<?php

namespace App\Services\Auth;

use App\Enum\UserLog;
use App\Enum\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginService extends Controller
{
    public static function AuthLogin($request)
    {
        $request->validated();

        if (Auth::attempt($request->only(['email', 'password']))) {
            $user = User::where('email', $request->email)->first();

            if (app(self::class)->isAccountUnverifiedOrInactive($user, $request)) {
                return app(self::class)->handleAccountIssues($user, $request, "Account not verified or inactive", UserLog::LOGIN_ATTEMPT);
            }

            if (app(self::class)->isAccountPending($user, $request)) {
                return app(self::class)->handleAccountIssues($user, $request, "Account not verified or inactive", UserLog::LOGIN_ATTEMPT, UserStatus::PENDING);
            }

            if (app(self::class)->isAccountSuspended($user, $request)) {
                return app(self::class)->handleAccountIssues($user, $request, "Account is suspended, contact support", UserLog::LOGIN_ATTEMPT, UserStatus::SUSPENDED);
            }

            if (app(self::class)->isAccountBlocked($user, $request)) {
                return app(self::class)->handleAccountIssues($user, $request, "Account is blocked, contact support", UserLog::LOGIN_ATTEMPT, UserStatus::BLOCKED);
            }

            if (!$user->is_admin_approve) {
                return app(self::class)->handleAccountIssues($user, $request, "Account not approved, contact support", UserLog::LOGIN_ATTEMPT, UserStatus::BLOCKED);
            }

            if ($user->two_factor_enabled) {
                return app(self::class)->handleTwoFactorAuthentication($user, $request);
            }
            return app(self::class)->logUserIn($user, $request);
        }

        return app(self::class)->handleInvalidCredentials($request);
    }
}

