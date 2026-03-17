<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthGates
{
    public function __construct(private readonly AuthManager $authManager) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->authManager->guard('admin')->user();
        if ($user instanceof Admin) {
            $user->load('roles.permissions');
        }

        return $next($request);
    }
}
