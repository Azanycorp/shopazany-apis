<?php

namespace App\Services\Auth;

use App\Actions\UserLogAction;
use App\Enum\UserLog;
use App\Enum\UserStatus;
use App\Http\Controllers\Controller;
use App\Mail\LoginVerifyMail;
use App\Mail\SignUpVerifyMail;
use App\Models\User;
use App\Trait\HttpResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthService extends Controller
{
    use HttpResponse;

    public function login($request)
    {
        $request->validated();

        if (Auth::attempt($request->only(['email', 'password']))) {
            $user = User::where('email', $request->email)->first();

            if($user->email_verified_at === null && $user->verification_code !== null){
                $description = "Account not verified, user {$request->email}";
                $response = $this->error([
                    'id' => $user->id,
                    'status' => "pending"
                ], "Account not verified or inactive", 400);
                $action = UserLog::LOGIN_ATTEMPT;

                $this->logUserAction($request, $action, $description, $response, $user);

                return $response;
            }

            if($user->status === UserStatus::PENDING){
                $description = "Account is pending, user {$request->email}";
                $response = $this->error([
                    'id' => $user->id,
                    'status' => UserStatus::PENDING
                ], "Account not verified or inactive", 400);
                $action = UserLog::LOGIN_ATTEMPT;

                $this->logUserAction($request, $action, $description, $response, $user);

                return $response;
            }

            if($user->status === UserStatus::SUSPENDED){
                $description = "Account is suspended, user {$request->email}";
                $response = $this->error([
                    'id' => $user->id,
                    'status' => UserStatus::SUSPENDED
                ], "Account is suspended, contact support", 400);
                $action = UserLog::LOGIN_ATTEMPT;

                $this->logUserAction($request, $action, $description, $response, $user);

                return $response;
            }

            if($user->status === UserStatus::BLOCKED){

                $description = "Account is blocked, user {$request->email}";
                $response = $this->error([
                    'id' => $user->id,
                    'status' => UserStatus::BLOCKED
                ], "Account is blocked, contact support", 400);
                $action = UserLog::LOGIN_ATTEMPT;

                $this->logUserAction($request, $action, $description, $response, $user);
                return $response;
            }

            if($user->login_code_expires_at > now()) {
                return $this->error(null, "Please wait a few minutes before requesting a new code.", 400);
            }

            $code = $this->generateVerificationCode();
            $time = now()->addMinutes(5);

            $user->update([
                'login_code' => $code,
                'login_code_expires_at' => $time
            ]);

            Mail::to($request->email)->send(new LoginVerifyMail($user));

            $description = "Attempt to login by {$request->email}";
            $response = $this->success(null, "Code has been sent to your email address.");
            $action = UserLog::LOGIN_ATTEMPT;

            $this->logUserAction($request, $action, $description, $response, $user);

            return $response;
        }

        $description = "Login OTP sent to {$request->email}";
        $action = UserLog::LOGIN_ATTEMPT;
        $response = $this->error(null, 'Credentials do not match', 401);

        $this->logUserAction($request, $action, $description, $response);
        return $response;
    }

    public function loginVerify($request)
    {
        $user = User::where('email', $request->email)
        ->where('login_code', $request->code)
        ->where('login_code_expires_at', '>', now())
        ->first();

        if(!$user){
            return $this->error(null, "Data not found.", 404);
        }

        $user->update([
            'login_code' => null,
            'login_code_expires_at' => null
        ]);

        $token = $user->createToken('API Token of '. $user->email);

        $description = "User with email {$request->email} logged in";
        $action = UserLog::LOGGED_IN;
        $response = $this->success([
            'user_id' => $user->id,
            'user_type' => $user->type,
            'has_signed_up' => true,
            'is_affiliate_member' => $user->is_affiliate_member === 1 ? true : false,
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ]);

        $this->logUserAction($request, $action, $description, $response, $user);

        return $response;
    }

    public function signup($request)
    {
        $request->validated($request->all());

        try {
            $code = $this->generateVerificationCode();

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'type' => 'customer',
                'email_verified_at' => null,
                'verification_code' => $code,
                'is_verified' => 0,
                'password' => bcrypt($request->password)
            ]);

            Mail::to($request->email)->send(new SignUpVerifyMail($user));

            $description = "User with email: {$request->email} signed up";
            $response = $this->success(null, "Created successfully");
            $action = UserLog::CREATED;

            $this->logUserAction($request, $action, $description, $response, $user);

            return $response;
        } catch (\Exception $e) {
            $description = "Sign up failed: {$request->email}";
            $response = $this->error(null, $e->getMessage(), 500);
            $action = UserLog::CREATED;

            $this->logUserAction($request, $action, $description, $response, $user);

            return $response;
        }
    }

    public function resendCode($request)
    {
        $user = User::getUserEmail($request->email);

        if(!$user){
            return $this->error(null, "User not found", 404);
        }

        try {

            Mail::to($request->email)->send(new SignUpVerifyMail($user));

            $description = "User with email address {$request->email} has requested a code to be resent.";
            $action = UserLog::CODE_RESENT;
            $response = $this->success(null, "Code resent successfully");

            $this->logUserAction($request, $action, $description, $response, $user);

            return $response;
        } catch (\Exception $e) {
            $description = "An error occured during the request email: {$request->email}";
            $action = UserLog::CODE_RESENT;
            $response = $this->error(null, $e->getMessage(), 500);

            $this->logUserAction($request, $action, $description, $response, $user);
            return $response;
        }
    }

    public function sellerSignup($request)
    {
        $request->validated($request->all());

        try {
            $code = $this->generateVerificationCode();

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'middlename' => $request->other_name,
                'email' => $request->email,
                'address' => $request->address,
                'country' => $request->country_id,
                'state_id' => $request->state_id,
                'type' => 'seller',
                'email_verified_at' => null,
                'verification_code' => $code,
                'is_verified' => 0,
                'password' => bcrypt($request->password)
            ]);

            Mail::to($request->email)->send(new SignUpVerifyMail($user));

            $description = "Seller with email address {$request->email} just signed up";
            $action = UserLog::CREATED;
            $response = $this->success(null, "Created successfully");

            $this->logUserAction($request, $action, $description, $response, $user);

            return $this->success(null, "Created successfully");
        } catch (\Exception $e) {
            $description = "Sign up error for user with email {$request->email}";
            $action = UserLog::CREATED;
            $response = $this->error(null, $e->getMessage(), 500);

            $this->logUserAction($request, $action, $description, $response, $user);

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
            'verification_code' => null,
            'email_verified_at' => Carbon::now(),
            'status' => 'active'
        ]);

        $description = "User with email address {$request->email} verified OTP";
        $action = UserLog::CREATED;
        $response = $this->success(null, "Verified successfully");

        $this->logUserAction($request, $action, $description, $response, $user);

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

        $this->logUserAction($request, $action, $description, $response, $user);

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
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => bcrypt($request->password),
                ])->save();
            }
        );

        $description = "User with email address {$request->email} changed password successfully";
        $action = UserLog::PASSWORD_RESET;
        $response = $this->success(null, "Reset successfully");

        $this->logUserAction($request, $action, $description, $response, $user);

        return $status == Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 500);
    }

    public function logout() {

        $user = request()->user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();

        $description = "User with email address {$user->email} logged out";
        $action = UserLog::LOGOUT;
        $response = $this->success([
            'message' => 'You have successfully logged out and your token has been deleted'
        ]);

        $this->logUserAction(request(), $action, $description, $response, $user);

        return $response;
    }

    public function affiliateSignup($request)
    {
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

                    $this->logUserAction($request, $action, $description, $response, $user);
                    return $response;
                }
            }

            DB::transaction(function () use ($request, $user) {
                $referrer_code = $this->determineReferrerCode($request);

                $referrer_link = $this->generateReferrerLink($referrer_code);
                $code = $this->generateVerificationCode();

                $data = $this->userTrigger($user, $request, $referrer_link, $referrer_code, $code);

                if ($request->referrer_code) {
                    $this->handleReferrer($request->referrer_code, $data);
                }
            });

            return $this->success(null, "Created successfully");
        } catch (\Exception $e) {
            $description = "User creation failed";
            $action = UserLog::CREATED;
            $response = $this->error(null, $e->getMessage(), 500);

            $this->logUserAction($request, $action, $description, $response, $user);
            return $response;
        }
    }

    private function determineReferrerCode($request)
    {
        $initial_referrer_code = Str::random(10);

        if ($request->referrer_code) {
            if (User::where('referrer_code', $request->referrer_code)->exists()) {
                return $this->generateUniqueReferrerCode();
            } else {
                return $request->referrer_code;
            }
        }

        return $initial_referrer_code;
    }

    private function generateReferrerLink($referrer_code)
    {
        return config('services.frontend_baseurl') . '/register?referrer=' . $referrer_code;
    }

    private function generateVerificationCode()
    {
        return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function handleExistingUser($user)
    {
        if ($user) {
            return $this->getUserReferrer($user);
        }

        return null;
    }

    private function handleReferrer($referrer_code, $data)
    {
        $referrer = User::where('referrer_code', $referrer_code)->first();
        if ($referrer) {
            $commission = 0.05 * 100;
            $referrer->wallet()->increment('balance', $commission);

            $referrer->referrer()->attach($data);
            $referrer->save();
        }
    }

    private function userTrigger($user, $request, $referrer_link, $referrer_code, $code)
    {
        if ($user) {
            $emailVerified = $user->email_verified_at;

            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'type' => 'customer',
                'referrer_code' => $referrer_code,
                'referrer_link' => $referrer_link,
                'is_verified' => 1,
                'is_affiliate_member' => 1,
                'password' => bcrypt($request->password)
            ]);

            $user->wallet()->create([
                'balance' => 0.00,
                'reward_point' => null
            ]);

            $description = "User with email {$request->email} signed up as an affiliate";
            $action = UserLog::CREATED;
            $response = $this->success(null, "Created successfully");

            $this->logUserAction($request, $action, $description, $response, $user);

            if (is_null($emailVerified)) {
                $user->update(['email_verified_at' => null, 'verification_code' => $code,]);
                try {
                    Mail::to($request->email)->send(new SignUpVerifyMail($user));
                } catch (\Exception $e) {
                    return $this->error(null, 'Unable to send verification email. Please try again later', 500);
                }
            }

        } else {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'type' => 'customer',
                'referrer_code' => $referrer_code,
                'referrer_link' => $referrer_link,
                'email_verified_at' => null,
                'verification_code' => $code,
                'is_verified' => 0,
                'is_affiliate_member' => 1,
                'password' => bcrypt($request->password)
            ]);

            $user->wallet()->create([
                'balance' => 0.00,
                'reward_point' => null
            ]);

            try {
                Mail::to($request->email)->send(new SignUpVerifyMail($user));
            } catch (\Exception $e) {
                return $this->error(null, 'Unable to send verification email. Please try again later', 500);
            }

            $description = "User with email {$request->email} signed up as an affiliate";
            $action = UserLog::CREATED;
            $response = $this->success(null, "Created successfully");

            $this->logUserAction($request, $action, $description, $response, $user);

            return $user;
        }
    }
}

