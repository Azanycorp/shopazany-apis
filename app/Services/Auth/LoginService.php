<?php

namespace App\Services\Auth;

use App\Enum\UserType;
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
        $auth = resolve(ServicesAuth::class);
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

    public static function syncLocalUserToAuthService($user, string $password)
    {
        $auth = resolve(ServicesAuth::class);

        try {
            $options = new RequestOptions(
                headers: [
                    config('services.auth_service.key') => config('services.auth_service.value'),
                ],
                data: [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'type' => $user->type === UserType::CUSTOMER ? 'b2c_customer' : 'b2c_seller',
                    'country_id' => $user->country,
                    'state_id' => $user->state_id,
                    'password' => $password,
                    'signed_up_from' => 'azany_b2c',
                    'email_verified_at' => now(),
                    'is_verified' => true,
                    'status' => 'active',
                ]
            );

            return $auth->post(config('services.auth_service.url').'/register', $options);
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ];
        }
    }

    public static function biometricLogin($request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return resolve(self::class)->handleBiometricsIssue($request, 'User not found!', 404);
        }

        if (! $user->biometric_enabled) {
            return resolve(self::class)->handleBiometricsIssue($request, 'Biometrics not enabled.', 401);
        }

        return resolve(self::class)->logUserIn($user, $request);
    }
}
