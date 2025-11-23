<?php

namespace App\Pipelines\Verify;

use App\Enum\MailingEnum;
use App\Enum\UserLog;
use App\Enum\UserStatus;
use App\Mail\UserWelcomeMail;
use App\Models\User;
use App\Trait\HttpResponse;
use App\Trait\SignUp;
use Closure;

class Verify
{
    use HttpResponse, SignUp;

    public function handle($request, Closure $next)
    {
        $user = User::where('email', $request->string('email'))
            ->where('verification_code', $request->string('code'))
            ->first();

        if (! $user) {
            return $this->error(null, 'Invalid code', 404);
        }

        $user->update([
            'is_verified' => 1,
            'is_admin_approve' => 1,
            'verification_code' => null,
            'email_verified_at' => now(),
            'status' => UserStatus::ACTIVE,
        ]);

        if ($user->pending_referrer_code !== null) {
            $this->handleReferrers($user->pending_referrer_code, $user);
            $user->update(['pending_referrer_code' => null]);
        }

        $type = MailingEnum::EMAIL_VERIFICATION;
        $subject = 'Email verification';
        $mail_class = UserWelcomeMail::class;
        $data = [
            'user' => $user,
        ];
        mailSend($type, $user, $subject, $mail_class, $data);

        $user->tokens()->delete();
        $token = $user->createToken('API Token of '.$user->email);

        $description = "User with email address {$request->email} verified OTP";
        $action = UserLog::CREATED;
        $response = $this->success([
            'user_id' => $user->id,
            'user_type' => $user->type,
            'has_signed_up' => true,
            'is_affiliate_member' => $user->is_affiliate_member === 1,
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ], 'Verified successfully');

        logUserAction($request, $action, $description, $response, $user);

        $request->merge(['response' => $response]);

        return $next($request);
    }
}
