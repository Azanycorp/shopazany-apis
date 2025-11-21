<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthCheck
{
    public function __construct(private readonly \Illuminate\Auth\AuthManager $authManager, private readonly \Illuminate\Contracts\Routing\ResponseFactory $responseFactory) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->authManager->check()) {
            return $this->responseFactory->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
