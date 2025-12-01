<?php

namespace App\Services\AgriEcom;

use App\Enum\MailingEnum;
use App\Enum\UserLog;
use App\Enum\UserStatus;
use App\Mail\SignUpVerifyMail;
use App\Models\User;
use App\Pipelines\Signup\Customer\CreateWithAuthService;
use App\Pipelines\Signup\Seller\Agriecom\CreateSeller;
use App\Pipelines\Verify\Verify;
use App\Pipelines\Verify\VerifyWithAuthService;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\Pipeline;

class AuthService
{
    use HttpResponse;

    public function register($request)
    {
        $request->validated($request->all());
        $request->merge([
            'first_name' => 'N/A',
            'last_name' => 'N/A',
            'type' => 'agriecom_b2b_seller',
            'country_id' => 160,
        ]);

        return Pipeline::send($request)
            ->withinTransaction()
            ->through([
                CreateWithAuthService::class,
                CreateSeller::class,
            ])
            ->thenReturn();
    }

    public function verify($request)
    {
        return Pipeline::send($request)
            ->withinTransaction()
            ->through([
                Verify::class,
                VerifyWithAuthService::class,
            ])
            ->thenReturn();
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
