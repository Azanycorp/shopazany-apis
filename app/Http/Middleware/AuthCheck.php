<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthCheck
{
    public function __construct(private readonly AuthManager $authManager, private readonly ResponseFactory $responseFactory) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->authManager->check()) {
            return $this->responseFactory->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
