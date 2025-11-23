<?php

namespace App\Trait;

use App\Enum\MailingEnum;
use App\Enum\UserLog;
use App\Enum\UserStatus;
use App\Mail\LoginVerifyMail;

trait Login
{
    use HttpResponse;

    protected function isAccountUnverifiedOrInactive($user): bool
    {
        return $user->email_verified_at === null && $user->verification_code !== null;
    }

    protected function isAccountPending($user): bool
    {
        return $user->status === UserStatus::PENDING;
    }

    protected function isAccountSuspended($user): bool
    {
        return $user->status === UserStatus::SUSPENDED;
    }

    protected function isAccountBlocked($user): bool
    {
        return $user->status === UserStatus::BLOCKED;
    }

    protected function handleAccountIssues($user, $request, $message, $action, $status = null)
    {
        $status = $status ?? 'pending';
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
            return $this->error(null, 'Please wait a few minutes before requesting a new code.', 400);
        }

        $user->update([
            'login_code' => generateVerificationCode(),
            'login_code_expires_at' => now()->addMinutes(10),
        ]);

        $type = MailingEnum::LOGIN_OTP;
        $subject = 'Login OTP';
        $mail_class = LoginVerifyMail::class;
        $data = [
            'user' => $user,
        ];
        mailSend($type, $user, $subject, $mail_class, $data);

        $description = "Attempt to login by {$request->email}";
        $response = $this->success([
            'user_id' => $user->id,
            'user_type' => $user->type,
            'two_factor_enabled' => $user->two_factor_enabled,
        ], 'Code has been sent to your email address.');
        $action = UserLog::LOGIN_ATTEMPT;

        logUserAction($request, $action, $description, $response, $user);

        return $response;
    }

    protected function logUserIn($user, $request)
    {
        $user->tokens()->delete();
        $token = $user->createToken("API Token of {$user->email}");

        $description = "User with email {$request->email} logged in";
        $response = $this->success([
            'user_id' => $user->id,
            'user_type' => $user->type,
            'has_signed_up' => true,
            'is_affiliate_member' => $user->is_affiliate_member,
            'two_factor_enabled' => $user->two_factor_enabled,
            'is_biometric_enabled' => $user->biometric_enabled,
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ], 'Login successful.');

        logUserAction($request, UserLog::LOGGED_IN, $description, $response, $user);

        return $response;
    }

    protected function handleInvalidCredentials($request)
    {
        $description = "Credentials do not match {$request->email}";
        $response = $this->error(null, 'Credentials do not match', 401);
        logUserAction($request, UserLog::LOGIN_ATTEMPT, $description, $response);

        return $response;
    }

    protected function handleBiometricsIssue($request, $message, $code = 400)
    {
        $response = $this->error(null, $message, $code);
        logUserAction($request, UserLog::LOGIN_ATTEMPT, $message, $response);

        return $response;
    }
}
