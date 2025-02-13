<?php

namespace App\Trait;

use App\Enum\MailingEnum;
use App\Enum\UserLog;
use App\Enum\UserStatus;

trait Login
{
    use HttpResponse;

    protected function isAccountUnverifiedOrInactive($user, $request): bool
    {
        return $user->email_verified_at === null && $user->verification_code !== null;
    }

    protected function isAccountPending($user, $request): bool
    {
        return $user->status === UserStatus::PENDING;
    }

    protected function isAccountSuspended($user, $request): bool
    {
        return $user->status === UserStatus::SUSPENDED;
    }

    protected function isAccountBlocked($user, $request): bool
    {
        return $user->status === UserStatus::BLOCKED;
    }

    protected function handleAccountIssues($user, $request, $message, $action, $status = null)
    {
        $status = $status ?? "pending";
        $description = "Account issue for user {$request->email}";
        $response = $this->error([
            'id' => $user->id,
            'status' => $status,
        ], $message, 400);

        logUserAction($request, $action, $description, $response, $user);

        return $response;
    }

    protected function handleTwoFactorAuthentication($user, $request)
    {
        if ($user->login_code_expires_at > now()) {
            return $this->error(null, "Please wait a few minutes before requesting a new code.", 400);
        }

        $code = generateVerificationCode();
        $time = now()->addMinutes(10);

        $user->update([
            'login_code' => $code,
            'login_code_expires_at' => $time,
        ]);

        $type = MailingEnum::LOGIN_OTP;
        $subject = "Login OTP";
        $mail_class = "App\Mail\LoginVerifyMail";

        mailSend($type, $user, $subject, $mail_class);

        $description = "Attempt to login by {$request->email}";
        $response = $this->success(null, "Code has been sent to your email address.");
        $action = UserLog::LOGIN_ATTEMPT;

        logUserAction($request, $action, $description, $response, $user);

        return $response;
    }

    protected function logUserIn($user, $request)
    {
        $user->tokens()->delete();
        $token = $user->createToken('API Token of ' . $user->email);

        $description = "User with email {$request->email} logged in";
        $action = UserLog::LOGGED_IN;
        $response = $this->success([
            'user_id' => $user->id,
            'user_type' => $user->type,
            'has_signed_up' => true,
            'is_affiliate_member' => $user->is_affiliate_member === 1,
            'two_factor_enabled' => $user->two_factor_enabled === 1,
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ], 'Login successful.');

        logUserAction($request, $action, $description, $response, $user);

        return $response;
    }

    protected function handleInvalidCredentials($request)
    {
        $description = "Credentials do not match {$request->email}";
        $action = UserLog::LOGIN_ATTEMPT;
        $response = $this->error(null, 'Credentials do not match', 401);

        logUserAction($request, $action, $description, $response);
        return $response;
    }
}


