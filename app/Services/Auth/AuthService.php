<?php

namespace App\Services\Auth;

use App\Enum\MailingEnum;
use App\Enum\UserLog;
use App\Enum\UserStatus;
use App\Http\Controllers\Controller;
use App\Mail\LoginVerifyMail;
use App\Mail\SignUpVerifyMail;
use App\Models\User;
use App\Pipelines\Signup\Affiliate\CreateAffiliateUser;
use App\Pipelines\Signup\Customer\CreateUser;
use App\Pipelines\Signup\Customer\CreateWithAuthService;
use App\Pipelines\Signup\Seller\CreateSeller;
use App\Pipelines\Verify\Verify;
use App\Pipelines\Verify\VerifyWithAuthService;
use App\Trait\HttpResponse;
use App\Trait\SignUp;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Pipeline;

class AuthService extends Controller
{
    use HttpResponse, SignUp;

    public function __construct(
        private readonly \Illuminate\Auth\Passwords\PasswordBrokerManager $passwordBrokerManager,
        private readonly \Illuminate\Hashing\BcryptHasher $bcryptHasher,
        private readonly \Illuminate\Contracts\Routing\ResponseFactory $responseFactory
    ) {}

    public function login($request)
    {
        return LoginService::AuthLogin($request);
    }

    public function biometricLogin($request)
    {
        return LoginService::biometricLogin($request);
    }

    public function loginVerify($request)
    {
        $user = User::where('email', $request->email)
            ->where('login_code', $request->code)
            ->where('login_code_expires_at', '>', now())
            ->first();

        if (! $user) {
            return $this->error(null, "User doesn't exist or Code has expired.", 404);
        }

        $user->update([
            'login_code' => null,
            'login_code_expires_at' => null,
        ]);

        $user->tokens()->delete();
        $token = $user->createToken('API Token of '.$user->email);

        $description = "User with email {$request->email} logged in";
        $action = UserLog::LOGGED_IN;
        $response = $this->success([
            'user_id' => $user->id,
            'user_type' => $user->type,
            'has_signed_up' => true,
            'is_affiliate_member' => $user->is_affiliate_member === 1,
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ]);

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

            $user->update([
                'email_verified_at' => null,
                'verification_code' => generateVerificationCode(),
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

    public function loginResendCode($request)
    {
        $user = User::getUserEmail($request->email);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        if (! $user->two_factor_enabled) {
            return $this->error(null, 'Two factor authentication is not enabled', 400);
        }

        if ($user->login_code_expires_at > now()) {
            return $this->error(null, 'Please wait a few minutes before requesting a new code.', 400);
        }

        try {

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

    public function signup($request)
    {
        $request->validated($request->all());
        $request->merge(['type' => 'b2c_customer']);

        return Pipeline::send($request)
            ->withinTransaction()
            ->through([
                CreateWithAuthService::class,
                CreateUser::class,
            ])
            ->thenReturn();
    }

    public function sellerSignup($request)
    {
        $request->validated($request->all());
        $request->merge(['type' => 'b2c_seller']);

        return Pipeline::send($request)
            ->withinTransaction()
            ->through([
                CreateWithAuthService::class,
                CreateSeller::class,
            ])
            ->thenReturn();
    }

    public function affiliateSignup($request)
    {
        $request->merge(['type' => 'b2c_seller']);

        return Pipeline::send($request)
            ->withinTransaction()
            ->through([
                CreateWithAuthService::class,
                CreateAffiliateUser::class,
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

    public function forgot($request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return $this->error('error', 'We can\'t find a user with that email address', 404);
        }

        $status = $this->passwordBrokerManager->broker('users')->sendResetLink(
            $request->only('email')
        );

        $description = "User with email address {$request->email} requested for password change";
        $action = UserLog::PASSWORD_FORGOT;
        $response = $this->success(null, 'Request successfully');

        logUserAction($request, $action, $description, $response, $user);

        return $status === Password::RESET_LINK_SENT
            ? $this->responseFactory->json(['message' => __($status)])
            : $this->responseFactory->json(['message' => __($status)], 500);
    }

    public function reset($request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return $this->error(null, 'We can\'t find a user with that email address', 404);
        }

        $status = $this->passwordBrokerManager->broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request): void {
                $user->forceFill([
                    'password' => $this->bcryptHasher->make($request->password),
                ])->save();
            }
        );

        if (! $user->is_verified && ! $user->is_admin_approve && blank($user->email_verified_at)) {
            $user->update([
                'is_verified' => 1,
                'is_admin_approve' => 1,
                'verification_code' => null,
                'email_verified_at' => now(),
                'status' => UserStatus::ACTIVE,
            ]);
        }

        $description = "User with email address {$request->email} changed password successfully";
        $action = UserLog::PASSWORD_RESET;
        $response = $this->success(null, 'Reset successfully');

        logUserAction($request, $action, $description, $response, $user);

        return $status == Password::PASSWORD_RESET
            ? $this->responseFactory->json(['message' => __($status)])
            : $this->responseFactory->json(['message' => __($status)], 500);
    }

    public function logout($request)
    {
        $user = $request->user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();

        $description = "User with email address {$user->email} logged out";
        $action = UserLog::LOGOUT;
        $response = $this->success([
            'message' => 'You have successfully logged out and your token has been deleted',
        ]);

        logUserAction($request, $action, $description, $response, $user);

        return $response;
    }
}
