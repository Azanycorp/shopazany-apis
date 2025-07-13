<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminCheck
{
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

        if ($user && $user->type !== 'super_admin') {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        return $next($request);
    }
}
