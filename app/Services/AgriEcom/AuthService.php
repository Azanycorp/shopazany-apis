<?php

namespace App\Services\AgriEcom;

use App\Enum\MailingEnum;
use App\Enum\UserLog;
use App\Enum\UserStatus;
use App\Enum\UserType;
use App\Mail\SignUpVerifyMail;
use App\Mail\UserWelcomeMail;
use App\Models\User;
use App\Trait\HttpResponse;

class AuthService
{
    use HttpResponse;

    public function __construct(private readonly \Illuminate\Hashing\BcryptHasher $bcryptHasher) {}

    public function register($request)
    {
        $code = generateVerificationCode();

        $user = User::query()->create([
            'email' => $request->input('email'),
            'type' => UserType::AGRIECOM_SELLER,
            'email_verified_at' => null,
            'verification_code' => $code,
            'is_verified' => 0,
            'password' => $this->bcryptHasher->make($request->password),
        ]);

        $description = "User with email: {$request->email} signed up";
        $response = $this->success(null, 'Created successfully', 201);
        $action = UserLog::CREATED;

        logUserAction($request, $action, $description, $response, $user);

        return $response;
    }

    public function verify($request)
    {
        $user = User::where('email', $request->email)
            ->where('verification_code', $request->code)
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

        $type = MailingEnum::EMAIL_VERIFICATION;
        $subject = 'Welcome Email';
        $mail_class = UserWelcomeMail::class;
        $data = [
            'user' => $user,
        ];
        mailSend($type, $user, $subject, $mail_class, $data);

        $description = "User with email address {$request->email} verified OTP";
        $action = UserLog::CREATED;
        $response = $this->success([
            'user_id' => $user->id,
            'user_type' => $user->type,
            'has_signed_up' => true,
        ], 'Verified successfully');

        logUserAction($request, $action, $description, $response, $user);

        return $response;
    }

    public function resendCode($request)
    {
        $user = User::getUserEmail($request->email);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        if ($user->email_verified_at !== null && $user->status === UserStatus::ACTIVE) {
            return $this->error(null, 'Account has been verified', 400);
        }

        try {

            $code = generateVerificationCode();

            $user->update([
                'email_verified_at' => null,
                'verification_code' => $code,
            ]);

            $type = MailingEnum::RESEND_CODE;
            $subject = 'Resend code';
            $mail_class = SignUpVerifyMail::class;
            $data = [
                'user' => $user,
            ];
            mailSend($type, $user, $subject, $mail_class, $data);

            $description = "User with email address {$request->email} has requested a code to be resent.";
            $action = UserLog::CODE_RESENT;
            $response = $this->success(null, 'Code resent successfully');

            logUserAction($request, $action, $description, $response, $user);

            return $response;
        } catch (\Exception $e) {
            $description = "An error occured during the request email: {$request->email}";
            $action = UserLog::FAILED;
            $response = $this->error(null, $e->getMessage(), 500);

            logUserAction($request, $action, $description, $response, $user);

            return $response;
        }
    }
}
