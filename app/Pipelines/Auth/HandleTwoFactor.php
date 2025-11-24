<?php

namespace App\Pipelines\Auth;

use App\Trait\Login;
use Closure;

class HandleTwoFactor
{
    use Login;

    public function handle($request, Closure $next)
    {
        if ($request->user->two_factor_enabled) {
            return $this->handleTwoFactorAuthentication($request->user, $request);
        }

        return $next($request);
    }
}
