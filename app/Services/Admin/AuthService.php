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

    public function __construct(
        private readonly \Illuminate\Auth\Passwords\PasswordBrokerManager $passwordBrokerManager,
        private readonly \Illuminate\Contracts\Routing\ResponseFactory $responseFactory,
        private readonly \Illuminate\Hashing\BcryptHasher $bcryptHasher
    ) {}

    public function login($request)
    {
        $request->validated();

        if (Auth::guard('admin')->attempt($request->only(['email', 'password']))) {
            $user = Admin::with('roles.permissions')->where('email', $request->email)->first();

            if ($user->status === LoginStatus::INACTIVE) {
                return $this->error([
                    'id' => $user->id,
                    'status' => LoginStatus::INACTIVE,
                ], 'Account inactive', 400);
            }

            $token = $user->createToken("API Token of {$user->email}", ['admin:access']);

            return $this->success([
                'id' => $user->id,
                'token' => $token->plainTextToken,
                'expires_at' => $token->accessToken->expires_at,
                'role' => $user->roles ? $user->roles->map(function ($role): array {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'permissions' => $role->permissions->flatMap(function ($permission): array {
                            return [$permission->name];
                        })->all(),
                    ];
                })->all() : [],
                'user_permissions' => $user->permissions ? $user->permissions->flatMap(function ($permission): array {
                    return [$permission->name];
                })->all() : [],
            ]);
        }

        return $this->error(null, 'Credentials do not match', 401);
    }

    public function forgot($request)
    {
        $user = Admin::where('email', $request->email)->first();

        if (! $user) {
            return $this->error('error', 'We can\'t find a user with that email address', 404);
        }

        $status = $this->passwordBrokerManager->broker('admins')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? $this->responseFactory->json(['message' => __($status)])
            : $this->responseFactory->json(['message' => __($status)], 500);
    }

    public function reset($request)
    {
        $user = Admin::where('email', $request->email)->first();

        if (! $user) {
            return $this->error('error', 'We can\'t find a user with that email address', 404);
        }

        $status = $this->passwordBrokerManager->broker('admins')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request): void {
                $user->forceFill([
                    'password' => $this->bcryptHasher->make($request->password),
                ])->save();
            }
        );

        return $status == Password::PASSWORD_RESET
            ? $this->responseFactory->json(['message' => __($status)])
            : $this->responseFactory->json(['message' => __($status)], 500);
    }
}
