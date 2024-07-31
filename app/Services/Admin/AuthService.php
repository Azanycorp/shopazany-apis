<?php

namespace App\Services\Admin;

use App\Enum\LoginStatus;
use App\Models\Admin;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class AuthService
{
    use HttpResponse;

    public function login($request)
    {
        $request->validated();

        if (Auth::guard('admins')->attempt($request->only(['email', 'password']))) {
            $user = Admin::where('email', $request->email)->first();

            if($user->status === LoginStatus::INACTIVE){
                return $this->error([
                    'id' => $user->id,
                    'status' => LoginStatus::INACTIVE
                ], "Account inactive", 400);
            }

            $token = $user->createToken('API Token of '. $user->email);

            return $this->success([
                'user_type' => $user->type,
                'has_signed_up' => true,
                'is_affiliate_member' => $user->is_affiliate_member === 1 ? true : false,
                'token' => $token->plainTextToken,
                'expires_at' => $token->accessToken->expires_at
            ]);
        }

        return $this->error(null, 'Credentials do not match', 401);
    }

    public function forgot($request)
    {
        $user = Admin::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('error', 'We can\'t find a user with that email address', 404);
        }

        $status = Password::broker('admins')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 500);
    }

    public function reset($request)
    {
        $user = Admin::where('email', $request->email)->first();

        if (!$user) {
            return $this->error('error', 'We can\'t find a user with that email address', 404);
        }

        $status = Password::broker('admins')->reset(
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
}


