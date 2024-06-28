<?php

namespace App\Services\Auth;

use App\Mail\LoginVerifyMail;
use App\Mail\SignUpVerifyMail;
use App\Models\User;
use App\Trait\HttpResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class AuthService
{
    use HttpResponse;

    public function login($request)
    {
        $request->validated();

        if (Auth::attempt($request->only(['email', 'password']))) {
            $user = User::where('email', $request->email)->first();

            if($user->email_verified_at === null && $user->verification_code !== null){
                return $this->error(null, 400, "Account not verified or inactive");
            }
            
            $code = rand(000000, 999999);
            $time = now()->addMinutes(5);

            $user->update([
                'login_code' => $code,
                'login_code_expires_at' => $time
            ]);

            Mail::to($request->email)->send(new LoginVerifyMail($user));

            return $this->success(null, "Code has been sent to your email address.");
        }

        return $this->error('', 401, 'Credentials do not match');
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
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at
        ]);
    }

    public function signup($request)
    {
        $request->validated($request->all());

        try {
            $code = rand(000000, 999999);

            $user = User::create([
                'name' => $request->fullname,
                'email' => $request->email,
                'type' => 'customer',
                'email_verified_at' => null,
                'verification_code' => $code,
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
            return $this->error(null, 404, "Invalid code");
        }

        $user->update([
            'verification_code' => null,
            'email_verified_at' => Carbon::now()
        ]);

        return $this->success(null, "Verified successfully");
    }

    public function forgot($request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('error', 404, 'We can\'t find a user with that email address');
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
            return $this->error('error', 404, 'We can\'t find a user with that email address');
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
}


