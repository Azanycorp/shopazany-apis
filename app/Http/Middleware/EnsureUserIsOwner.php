<?php

namespace App\Http\Middleware;

use App\Trait\HttpResponse;
use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOwner
{
    use HttpResponse;

    public function __construct(private readonly AuthManager $authManager) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routeUserId = $request->route('user_id') ?? $request->input('user_id');

        if ($this->authManager->id() != $routeUserId) {
            return $this->error(null, 'You are not authorized to access this resource', 403);
        }

        return $next($request);
    }
}
