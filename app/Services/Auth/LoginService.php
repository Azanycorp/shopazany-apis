<?php

namespace App\Services\Auth;

use App\Enum\UserLog;
use App\Enum\UserStatus;
use App\Models\User;
use App\Trait\Login;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;

class LoginService
{
    use Login;

    public static function AuthLogin($request)
    {
        $request->validated();

        if (Auth::attempt($request->only(['email', 'password']))) {
            $user = User::where('email', $request->email)->first();

            if ((new Application)->make(self::class)->isAccountUnverifiedOrInactive($user, $request)) {
                return (new Application)->make(self::class)->handleAccountIssues($user, $request, 'Account not verified or inactive', UserLog::LOGIN_ATTEMPT);
            }

            if ((new Application)->make(self::class)->isAccountPending($user, $request)) {
                return (new Application)->make(self::class)->handleAccountIssues($user, $request, 'Account not verified or inactive', UserLog::LOGIN_ATTEMPT, UserStatus::PENDING);
            }

            if ((new Application)->make(self::class)->isAccountSuspended($user, $request)) {
                return (new Application)->make(self::class)->handleAccountIssues($user, $request, 'Account is suspended, contact support', UserLog::LOGIN_ATTEMPT, UserStatus::SUSPENDED);
            }

            if ((new Application)->make(self::class)->isAccountBlocked($user, $request)) {
                return (new Application)->make(self::class)->handleAccountIssues($user, $request, 'Account is blocked, contact support', UserLog::LOGIN_ATTEMPT, UserStatus::BLOCKED);
            }

            if (! $user->is_admin_approve) {
                return (new Application)->make(self::class)->handleAccountIssues($user, $request, 'Account not approved, contact support', UserLog::LOGIN_ATTEMPT, UserStatus::BLOCKED);
            }

            if ($user->two_factor_enabled) {
                return (new Application)->make(self::class)->handleTwoFactorAuthentication($user, $request);
            }

            return (new Application)->make(self::class)->logUserIn($user, $request);
        }

        return (new Application)->make(self::class)->handleInvalidCredentials($request);
    }

    public static function biometricLogin($request)
    {
        $user = User::find($request->user_id);

        if (! $user) {
            return (new Application)->make(self::class)->handleBiometricsIssue($request, 'User not found!', 404);
        }

        if (! $user->biometric_enabled || ! (new \Illuminate\Contracts\Hashing\Hasher)->check($request->token, $user->biometric_token)) {
            return (new Application)->make(self::class)->handleBiometricsIssue($request, 'Invalid biometric credentials.', 401);
        }

        return (new Application)->make(self::class)->logUserIn($user, $request);
    }
}
