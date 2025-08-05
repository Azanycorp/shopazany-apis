<?php

namespace App\Services\Admin;

use App\Enum\AdminType;
use App\Enum\LoginStatus;
use App\Enum\MailingEnum;
use App\Mail\AccountVerificationEmail;
use App\Models\Admin;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\Auth;

class SuperAdminAuthService
{
    use HttpResponse;

    public function login($request)
    {
        if (Auth::guard('admin')->attempt($request->only(['email', 'password']))) {
            $user = Admin::where('email', $request->email)->first();

            if ($user->status === LoginStatus::INACTIVE) {
                return $this->error([
                    'id' => $user->id,
                    'status' => LoginStatus::INACTIVE,
                ], 'Account inactive', 400);
            }

            if ($user->type !== AdminType::SUPER_ADMIN) {
                return $this->error(null, 'You are not authorized to access this resource', 401);
            }

            $token = $user->createToken('API Token for '.$user->email);

            return $this->success([
                'id' => $user->id,
                'token' => $token->plainTextToken,
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'status' => $user->status,
                ]
            ]);
        }

        return $this->error(null, 'Credentials do not match', 401);
    }

    public function forgot($request)
    {
        $user = Admin::where('email', $request->email)->first();

        if (! $user) {
            return $this->error('error', 'We can\'t find a user with that email address', 404);
        }

        if ($user->type !== AdminType::SUPER_ADMIN) {
            return $this->error(null, 'You are not authorized to access this resource', 401);
        }

        $code = generateVerificationCode(4);

        $user->update([
            'verification_code' => $code,
            'verification_code_expire_at' => now()->addMinutes(10),
        ]);

        $type = MailingEnum::ACCOUNT_VERIFICATION;
        $subject = 'Verify Account';
        $mail_class = AccountVerificationEmail::class;
        $data = [
            'user' => $user,
        ];
        mailSend($type, $user, $subject, $mail_class, $data);

        return $this->success(null, "We have sent a verification code to your email");
    }

    public function verifyEmail($request)
    {
        $user = Admin::where('email', $request->email)
            ->first();

        if (! $user) {
            return $this->error(null, 'Invalid code', 404);
        }

        if ($user->verification_code !== $request->code) {
            return $this->error(null, 'Invalid code', 404);
        }

        if ($user->verification_code_expire_at < now()) {
            return $this->error(null, 'Code expired', 400);
        }

        $user->update([
            'verification_code' => null,
            'verification_code_expire_at' => null,
        ]);

        return $this->success(null, 'Email verified successfully');
    }

    public function reset($request)
    {
        $user = Admin::where('email', $request->email)->first();

        if (! $user) {
            return $this->error('error', 'We can\'t find a user with that email address', 404);
        }

        if ($user->type !== AdminType::SUPER_ADMIN) {
            return $this->error(null, 'You are not authorized to access this resource', 401);
        }

        $user->update([
            'password' => bcrypt($request->password),
        ]);

        return $this->success(null, 'Password reset successfully');
    }
}
