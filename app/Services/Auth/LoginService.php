<?php

namespace App\Services\Auth;

use App\Enum\UserLog;
use App\Enum\UserStatus;
use App\Models\User;
use App\Trait\Login;

class LoginService
{
    use Login;

    public static function AuthLogin($request)
    {
        $request->validated();

        if ((new \Illuminate\Auth\AuthManager)->attempt($request->only(['email', 'password']))) {
            $user = User::where('email', $request->email)->first();

            if ((new \Illuminate\Foundation\Application)->make(self::class)->isAccountUnverifiedOrInactive($user, $request)) {
                return (new \Illuminate\Foundation\Application)->make(self::class)->handleAccountIssues($user, $request, 'Account not verified or inactive', UserLog::LOGIN_ATTEMPT);
            }

            if ((new \Illuminate\Foundation\Application)->make(self::class)->isAccountPending($user, $request)) {
                return (new \Illuminate\Foundation\Application)->make(self::class)->handleAccountIssues($user, $request, 'Account not verified or inactive', UserLog::LOGIN_ATTEMPT, UserStatus::PENDING);
            }

            if ((new \Illuminate\Foundation\Application)->make(self::class)->isAccountSuspended($user, $request)) {
                return (new \Illuminate\Foundation\Application)->make(self::class)->handleAccountIssues($user, $request, 'Account is suspended, contact support', UserLog::LOGIN_ATTEMPT, UserStatus::SUSPENDED);
            }

            if ((new \Illuminate\Foundation\Application)->make(self::class)->isAccountBlocked($user, $request)) {
                return (new \Illuminate\Foundation\Application)->make(self::class)->handleAccountIssues($user, $request, 'Account is blocked, contact support', UserLog::LOGIN_ATTEMPT, UserStatus::BLOCKED);
            }

            if (! $user->is_admin_approve) {
                return (new \Illuminate\Foundation\Application)->make(self::class)->handleAccountIssues($user, $request, 'Account not approved, contact support', UserLog::LOGIN_ATTEMPT, UserStatus::BLOCKED);
            }

            if ($user->two_factor_enabled) {
                return (new \Illuminate\Foundation\Application)->make(self::class)->handleTwoFactorAuthentication($user, $request);
            }

            return (new \Illuminate\Foundation\Application)->make(self::class)->logUserIn($user, $request);
        }

        return (new \Illuminate\Foundation\Application)->make(self::class)->handleInvalidCredentials($request);
    }

    public static function biometricLogin($request)
    {
        $user = User::find($request->user_id);

        if (! $user) {
            return (new \Illuminate\Foundation\Application)->make(self::class)->handleBiometricsIssue($request, 'User not found!', 404);
        }

        if (! $user->biometric_enabled || ! (new \Illuminate\Contracts\Hashing\Hasher)->check($request->token, $user->biometric_token)) {
            return (new \Illuminate\Foundation\Application)->make(self::class)->handleBiometricsIssue($request, 'Invalid biometric credentials.', 401);
        }

        return (new \Illuminate\Foundation\Application)->make(self::class)->logUserIn($user, $request);
    }
}
