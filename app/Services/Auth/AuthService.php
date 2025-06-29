<?php

namespace App\Services\Auth;

use App\Enum\MailingEnum;
use App\Enum\UserLog;
use App\Enum\UserStatus;
use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Mail\SignUpVerifyMail;
use App\Mail\UserWelcomeMail;
use App\Models\Action;
use App\Models\User;
use App\Trait\HttpResponse;
use App\Trait\SignUp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthService extends Controller
{
    use HttpResponse, SignUp;

    public function login($request)
    {
        return LoginService::AuthLogin($request);
    }

    public function loginVerify($request)
    {
        $user = User::where('email', $request->email)
            ->where('login_code', $request->code)
            ->where('login_code_expires_at', '>', now())
            ->first();

        if(! $user){
            return $this->error(null, "User doesn't exist or Code has expired.", 404);
        }

        $user->update([
            'login_code' => null,
            'login_code_expires_at' => null
        ]);

        $user->tokens()->delete();
        $token = $user->createToken('API Token of '. $user->email);

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

    public function signup($request)
    {
        $request->validated($request->all());
        try {
            $user = $this->createUser($request);

            $description = "User with email: {$request->email} signed up";
            $response = $this->success(null, "Created successfully");
            $action = UserLog::CREATED;

            logUserAction($request, $action, $description, $response, $user);

            return $response;
        } catch (\Exception $e) {
            $description = "Sign up failed: {$request->email}";
            $response = $this->error(null, $e->getMessage(), 500);
            $action = UserLog::FAILED;

            logUserAction($request, $action, $description, $response, $user);

            return $response;
        }
    }

    public function resendCode($request)
    {
        $user = User::getUserEmail($request->email);

        if(!$user){
            return $this->error(null, "User not found", 404);
        }

        if($user->email_verified_at !== null && $user->status === UserStatus::ACTIVE) {
            return $this->error(null, "Account has been verified", 400);
        }

        try {

            $code = generateVerificationCode();

            $user->update([
                'email_verified_at' => null,
                'verification_code' => $code,
            ]);

            $type = MailingEnum::RESEND_CODE;
            $subject = "Resend code";
            $mail_class = SignUpVerifyMail::class;
            $data = [
                'user' => $user
            ];
            mailSend($type, $user, $subject, $mail_class, $data);

            $description = "User with email address {$request->email} has requested a code to be resent.";
            $action = UserLog::CODE_RESENT;
            $response = $this->success(null, "Code resent successfully");

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

    public function sellerSignup($request)
    {
        $request->validated($request->all());
        $user = null;

        $currencyCode = $this->currencyCode($request);
        $coupon = $request->query('coupon');
        $coupon = $this->normalizeCoupon($coupon);
        $referrer = $request->query('referrer');

        if ($coupon) {
            try {
                $this->validateCoupon($coupon);
            } catch (\Exception $e) {
                return $this->error(null, $e->getMessage(), 400);
            }
        }

        try {
            $code = generateVerificationCode();

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'middlename' => $request->other_name,
                'company_name' => $request->business_name,
                'email' => $request->email,
                'address' => $request->address,
                'country' => $request->country_id,
                'state_id' => $request->state_id,
                'type' => UserType::SELLER,
                'default_currency' => $currencyCode,
                'email_verified_at' => null,
                'verification_code' => $code,
                'is_verified' => 0,
                'password' => bcrypt($request->password)
            ]);

            if ($coupon) {
                $this->assignCoupon($coupon, $user);
            }

            if ($referrer) {
                $user->update(['pending_referrer_code' => $referrer]);
            }

            $description = "Seller with email address {$request->email} just signed up";
            $action = UserLog::CREATED;
            $response = $this->success(null, "Created successfully");

            logUserAction($request, $action, $description, $response, $user);

            return $this->success(null, "Created successfully");
        } catch (\Exception $e) {
            $description = "Sign up error for user with email {$request->email}";
            $action = UserLog::FAILED;
            $response = $this->error(null, $e->getMessage(), 500);

            logUserAction($request, $action, $description, $response, $user);

            return $response;
        }
    }

    public function verify($request)
    {
        $user = User::where('email', $request->email)
        ->where('verification_code', $request->code)
        ->first();

        if(!$user){
            return $this->error(null, "Invalid code", 404);
        }

        $user->update([
            'is_verified' => 1,
            'is_admin_approve' => 1,
            'verification_code' => null,
            'email_verified_at' => now(),
            'status' => UserStatus::ACTIVE
        ]);

        if ($user->pending_referrer_code) {
            $this->handleReferrers($user->pending_referrer_code, $user);
            $user->update(['pending_referrer_code' => null]);
        }

        $type = MailingEnum::EMAIL_VERIFICATION;
        $subject = "Email verification";
        $mail_class = UserWelcomeMail::class;
        $data = [
            'user' => $user
        ];
        mailSend($type, $user, $subject, $mail_class, $data);

        $user->tokens()->delete();
        $token = $user->createToken('API Token of '. $user->email);

        $description = "User with email address {$request->email} verified OTP";
        $action = UserLog::CREATED;
        $response = $this->success([
            'user_id' => $user->id,
            'user_type' => $user->type,
            'has_signed_up' => true,
            'is_affiliate_member' => $user->is_affiliate_member === 1,
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ], "Verified successfully");

        logUserAction($request, $action, $description, $response, $user);

        return $response;
    }

    public function forgot($request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('error', 'We can\'t find a user with that email address', 404);
        }

        $status = Password::broker('users')->sendResetLink(
            $request->only('email')
        );

        $description = "User with email address {$request->email} requested for password change";
        $action = UserLog::PASSWORD_FORGOT;
        $response = $this->success(null, "Request successfully");

        logUserAction($request, $action, $description, $response, $user);

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 500);
    }

    public function reset($request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('error', 'We can\'t find a user with that email address', 404);
        }

        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request): void {
                $user->forceFill([
                    'password' => bcrypt($request->password),
                ])->save();
            }
        );

        $description = "User with email address {$request->email} changed password successfully";
        $action = UserLog::PASSWORD_RESET;
        $response = $this->success(null, "Reset successfully");

        logUserAction($request, $action, $description, $response, $user);

        return $status == Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 500);
    }

    public function logout()
    {

        $user = request()->user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();

        $description = "User with email address {$user->email} logged out";
        $action = UserLog::LOGOUT;
        $response = $this->success([
            'message' => 'You have successfully logged out and your token has been deleted'
        ]);

        logUserAction(request(), $action, $description, $response, $user);

        return $response;
    }

    public function affiliateSignup($request)
    {
        $user = null;

        try {
            $user = User::where('email', $request->email)->first();
            $response = $this->handleExistingUser($user);

            if ($response) {
                return $response;
            }

            if ($request->referrer_code) {
                $referrer = User::where('referrer_code', $request->referrer_code)->first();

                if ($referrer && (!$referrer->email_verified_at || $referrer->is_verified != 1)) {
                    $description = "User with referral code and email {$referrer->email} has not been verified";
                    $action = UserLog::CREATED;
                    $response = $this->error(null, 'User with referral code has not been verified', 400);

                    logUserAction($request, $action, $description, $response, $user);
                    return $response;
                }
            }

            DB::transaction(function () use ($request, $user): void {
                $referrer_code = $this->determineReferrerCode($request);

                $referrer_link = generate_referrer_link($referrer_code);
                $code = generateVerificationCode();

                $data = $this->userTrigger($user, $request, $referrer_link, $referrer_code, $code);

                if ($request->referrer_code) {
                    $this->handleReferrer($request->referrer_code, $data);
                }
            });

            return $this->success(null, "Created successfully");
        } catch (\Exception $e) {
            $description = "User creation failed";
            $action = UserLog::FAILED;
            $response = $this->error(null, $e->getMessage(), 500);

            logUserAction($request, $action, $description, $response, $user);
            return $response;
        }
    }

    private function determineReferrerCode($request)
    {
        $initial_referrer_code = Str::random(10);
        if (!$request->referrer_code) {
            return $initial_referrer_code;
        }
        if (User::where('referrer_code', $request->referrer_code)->exists()) {
            return $this->generateUniqueReferrerCode();
        }
        return $request->referrer_code;
    }

    private function handleExistingUser($user)
    {
        if ($user) {
            return $this->getUserReferrer($user);
        }
        return null;
    }

    private function handleReferrer($referrer_code, $data): void
    {
        $referrer = User::with(['wallet', 'referrer'])
            ->where('referrer_code', $referrer_code)
            ->first();

        if (!$referrer || !$referrer->is_affiliate_member) {
            throw new \Exception('You are not a valid referrer');
        }

        $points = optional(Action::where('slug', 'create_account')->first())->points ?? 0;
        $referrer->wallet()->increment('reward_point', $points);
        $referrer->referrer()->attach($data);
        $referrer->save();
    }

    private function userTrigger($user, $request, $referrer_link, $referrer_code, $code)
    {
        $currencyCode = $this->currencyCode($request);
        if ($user) {
            $emailVerified = $user->email_verified_at;

            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'country' => $request->country_id,
                'state_id' => $request->state_id,
                'default_currency' => $currencyCode,
                'type' => UserType::SELLER,
                'referrer_code' => $referrer_code,
                'referrer_link' => $referrer_link,
                'is_verified' => 1,
                'is_affiliate_member' => 1,
                'password' => bcrypt($request->password)
            ]);

            $description = "User with email {$request->email} signed up as an affiliate";
            $action = UserLog::CREATED;
            $response = $this->success(null, "Created successfully");

            logUserAction($request, $action, $description, $response, $user);

            if (is_null($emailVerified)) {
                $user->update(['email_verified_at' => null, 'verification_code' => $code,]);

                // Send email to user to verify account
                $type = MailingEnum::SIGN_UP_OTP;
                $subject = "Verify Account";
                $mail_class = SignUpVerifyMail::class;
                $data = [
                    'user' => $user
                ];
                mailSend($type, $user, $subject, $mail_class, $data);
            }

        } else {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'type' => UserType::SELLER,
                'default_currency' => $currencyCode,
                'email_verified_at' => null,
                'verification_code' => $code,
                'country' => $request->country_id,
                'state_id' => $request->state_id,
                'is_verified' => 0,
                'is_affiliate_member' => 1,
                'password' => bcrypt($request->password)
            ]);

            $description = "User with email {$request->email} signed up as an affiliate";
            $action = UserLog::CREATED;
            $response = $this->success(null, "Created successfully");
            logUserAction($request, $action, $description, $response, $user);

            return $user;
        }
        return null;
    }
}

