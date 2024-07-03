<?php

namespace App\Services\Auth;

use App\Http\Controllers\Controller;
use App\Mail\LoginVerifyMail;
use App\Mail\SignUpVerifyMail;
use App\Models\User;
use App\Trait\HttpResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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
                return $this->error(null, "Account not verified or inactive", 400);
            }

            if($user->status === "pending"){
                return $this->error(null, "Account not verified or inactive", 400);
            }

            if($user->status === "suspended"){
                return $this->error(null, "Account is suspended, contact support", 400);
            }

            if($user->status === "blocked"){
                return $this->error(null, "Account is blocked, contact support", 400);
            }

            if($user->login_code_expires_at > now()) {
                return $this->error(null, "Please wait a few minutes before requesting a new code.", 400);
            }

            $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $time = now()->addMinutes(5);

            $user->update([
                'login_code' => $code,
                'login_code_expires_at' => $time
            ]);

            Mail::to($request->email)->send(new LoginVerifyMail($user));

            return $this->success(null, "Code has been sent to your email address.");
        }

        return $this->error(null, 'Credentials do not match', 401,);
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

        return $this->success([
            'has_signed_up' => true,
            'is_affiliate_member' => $user->is_affiliate_member === 1 ? true : false,
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at
        ]);
    }

    public function signup($request)
    {
        $request->validated($request->all());

        try {
            $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

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

            return $this->success(null, "Created successfully");
        } catch (\Exception $e) {
            return $this->error(null, 500, $e->getMessage());
        }
    }

    public function verify($request)
    {
        $user = User::where('email', $request->email)
        ->where('verification_code', $request->code)
        ->first();

        if(!$user){
            return $this->error(null, "Invalid code", 404,);
        }

        $user->update([
            'is_verified' => 1,
            'verification_code' => null,
            'email_verified_at' => Carbon::now(),
            'status' => 'active'
        ]);

        return $this->success(null, "Verified successfully");
    }

    public function forgot($request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('error', 'We can\'t find a user with that email address', 404,);
        }

        $status = Password::broker('users')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 500);
    }

    public function reset($request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('error', 'We can\'t find a user with that email address', 404,);
        }

        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => bcrypt($request->password),
                ])->save();
            }
        );

        return $status == Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 500);
    }

    public function logout() {

        $user = request()->user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();

        return $this->success([
            'message' => 'You have successfully logged out and your token has been deleted'
        ]);
    }

    public function affiliateSignup($request)
    {
        try {
            $initial_referrer_code = Str::random(10);
            $check_user = User::where('referrer_code', $initial_referrer_code)->exists();
    
            if ($check_user) {
                $referrer_code = $this->generateAlternateReferrerCode();
                while (User::where('referrer_code', $referrer_code)->exists()) {
                    $referrer_code = $this->generateAlternateReferrerCode();
                }
            } else {
                $referrer_code = $initial_referrer_code;
            }

            if ($request->referrer_code) {
                $referrer_code = $request->referrer_code;
            }
    
            $referrer_link = config('services.frontend_baseurl') . '/register?referrer=' . $referrer_code;
            $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

            $user = User::where('email', $request->email)->first();

            $response = $this->getUserReferrer($user);

            if ($response) {
                return $response;
            }

            $this->userTriger($user, $request, $referrer_link, $referrer_code, $code);
    
            return $this->success(null, "Created successfully");
    
        } catch (\Exception $e) {
            Log::error('User creation failed: ' . $e->getMessage());
    
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    private function userTriger($user, $request, $referrer_link, $referrer_code, $code)
    {
        if ($user) {
            $emailVerified = $user->email_verified_at;

            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'type' => 'customer',
                'referrer_code' => $referrer_code,
                'referrer_link' => $referrer_link,
                'is_verified' => 0,
                'password' => bcrypt($request->password)
            ]);

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
                'password' => bcrypt($request->password)
            ]);

            try {
                Mail::to($request->email)->send(new SignUpVerifyMail($user));
            } catch (\Exception $e) {
                return $this->error(null, 'Unable to send verification email. Please try again later', 500);
            }
        }
    }
}


