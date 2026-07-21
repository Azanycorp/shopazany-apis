<?php

namespace App\Pipelines\Auth;

use App\Enum\UserStatus;
use App\Enum\UserType;
use App\Models\User;
use App\Trait\HttpResponse;
use Closure;

class EnsureLocalUserExists
{
    use HttpResponse;

    public function handle($request, Closure $next)
    {
        $typeValue = $request->input('type');
        $typeValue = blank($typeValue) ? 'customer' : $typeValue;

        $type = $typeValue === 'customer'
            ? UserType::CUSTOMER
            : UserType::SELLER;

        $user = User::firstOrCreate(
            ['email' => $request->email],
            [
                'first_name' => $request->externalUser['first_name'] ?? null,
                'last_name' => $request->externalUser['last_name'] ?? null,
                'email' => $request->externalUser['email'] ?? null,
                'country' => $request->externalUser['country_id'] ?? null,
                'state_id' => $request->externalUser['state_id'] ?? null,
                'type' => $type,
                'password' => bcrypt($request->password),
                'status' => UserStatus::ACTIVE,
                'is_admin_approve' => true,
                'is_verified' => true,
                'email_verified_at' => now(),
            ]
        );

        $request->user = $user;

        return $next($request);
    }
}
