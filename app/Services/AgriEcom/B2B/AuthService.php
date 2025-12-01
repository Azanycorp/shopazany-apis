<?php

namespace App\Services\AgriEcom\B2B;

use App\Enum\MailingEnum;
use App\Enum\UserLog;
use App\Enum\UserStatus;
use App\Enum\UserType;
use App\Models\User;
use App\Pipelines\Signup\B2B\CreateB2BBuyer;
use App\Pipelines\Signup\B2B\CreateB2BSeller;
use App\Pipelines\Signup\B2B\CreateB2BWithAuthService;
use App\Services\Auth\LoginService;
use App\Trait\HttpResponse;
use App\Trait\SignUp;
use Illuminate\Support\Facades\Pipeline;

class AuthService
{
    use HttpResponse, SignUp;

    public function login($request)
    {
        return LoginService::AuthLogin($request);
    }

    public function signup($request)
    {
        $request->merge([
            'type' => UserType::B2B_AGRIECOM_SELLER,
        ]);

        return Pipeline::send($request)
            ->withinTransaction()
            ->through([
                CreateB2BWithAuthService::class,
                CreateB2BSeller::class,
            ])
            ->thenReturn();
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
        $subject = 'Email verification';
        $mail_class = "App\Mail\UserWelcomeMail";
        $data = [
            'user' => $user,
        ];
        mailSend($type, $user, $subject, $mail_class, $data);

        $description = "User with email address {$request->email} verified OTP";
        $action = UserLog::CREATED;
        $response = $this->success(['user_id' => $user->id], 'Verified successfully');

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
            $mail_class = "App\Mail\SignUpVerifyMail";
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

    public function buyerOnboarding($request)
    {
        $request->merge([
            'type' => UserType::B2B_AGRIECOM_BUYER,
        ]);

        return Pipeline::send($request)
            ->withinTransaction()
            ->through([
                CreateB2BWithAuthService::class,
                CreateB2BBuyer::class,
            ])
            ->thenReturn();
    }
}
