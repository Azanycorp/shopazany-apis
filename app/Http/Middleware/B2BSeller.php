<?php

namespace App\Http\Middleware;

use App\Enum\UserType;
use App\Trait\HttpResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class B2BSeller
{
    use HttpResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        if ($user && ! in_array($user->type, [UserType::B2B_SELLER, UserType::B2B_AGRIECOM_SELLER])) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        return $next($request);
    }
}
