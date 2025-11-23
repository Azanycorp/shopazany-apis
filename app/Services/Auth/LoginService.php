<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Pipelines\Auth\CheckAccountStatus;
use App\Pipelines\Auth\EnsureLocalUserExists;
use App\Pipelines\Auth\HandleTwoFactor;
use App\Pipelines\Auth\LogUserIn;
use App\Pipelines\Auth\ValidateExternalAuth;
use App\Services\Auth\Auth as ServicesAuth;
use App\Trait\Login;
use Illuminate\Support\Facades\Pipeline;

class LoginService
{
    use Login;

    public static function AuthLogin($request)
    {
        $request->validated();

        return Pipeline::send($request)
            ->through([
                ValidateExternalAuth::class,
                EnsureLocalUserExists::class,
                CheckAccountStatus::class,
                HandleTwoFactor::class,
                LogUserIn::class,
            ])
            ->thenReturn();
    }

    public static function externalAuthCheck($request)
    {
        $auth = app(ServicesAuth::class);
        $options = new RequestOptions(
            headers: [
                config('services.auth_service.key') => config('services.auth_service.value'),
            ],
            data: [
                'email' => $request->input('email'),
                'password' => $request->input('password'),
            ]
        );

        return $auth->post(config('services.auth_service.url').'/login', $options);
    }

    public static function biometricLogin($request)
    {
        $user = User::find($request->user_id);

        if (! $user) {
            return app(self::class)->handleBiometricsIssue($request, 'User not found!', 404);
        }

        if (! $user->biometric_enabled || ! (new \Illuminate\Contracts\Hashing\Hasher)->check($request->token, $user->biometric_token)) {
            return app(self::class)->handleBiometricsIssue($request, 'Invalid biometric credentials.', 401);
        }

        return app(self::class)->logUserIn($user, $request);
    }
}
