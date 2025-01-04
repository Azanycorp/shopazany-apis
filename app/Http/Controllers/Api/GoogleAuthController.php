<?php

namespace App\Http\Controllers\Api;

use App\Enum\UserStatus;
use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    protected $frontendBaseUrl;

    public function __construct()
    {
        if (app()->environment('production')) {
            $this->frontendBaseUrl = config('services.frontend_baseurl');
        } else {
            $this->frontendBaseUrl = config('services.staging_frontend_baseurl');
        }
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        try {
            $fullName = $googleUser->name;
            list($firstName, $lastName) = explode(' ', $fullName, 2);

            $user = User::where('email', $googleUser->getEmail())->first();

            if (! $user) {
                $user = User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $googleUser->email,
                    'password' => bcrypt('12345678'),
                    'type' => UserType::CUSTOMER,
                    'status' => UserStatus::ACTIVE,
                    'email_verified_at' => now(),
                    'is_verified' => 1,
                    'is_admin_approve' => 1,
                ]);
            } else {
                $user->update([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'status' => UserStatus::ACTIVE,
                ]);
            }

            $token = $user->createToken('token-name')->plainTextToken;

            return redirect($this->frontendBaseUrl . '/auth/callback?' . http_build_query([
                'token' => $token,
                'user' => $user,
            ]));

        } catch (\Exception $e) {
            return redirect($this->frontendBaseUrl . '/auth/callback?' . http_build_query([
                'error' => 'Authentication failed! ' . $e->getMessage(),
            ]));
        }
    }
}
