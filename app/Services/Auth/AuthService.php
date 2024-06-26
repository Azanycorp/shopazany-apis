<?php

namespace App\Services\Auth;

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

            $token = $user->createToken('API Token of '. $user->email);

            return $this->success([
                'token' => $token->plainTextToken,
                'expires_at' => $token->accessToken->expires_at
            ]);
        }

        return $this->error('', 401, 'Credentials do not match');
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


