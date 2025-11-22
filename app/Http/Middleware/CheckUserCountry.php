<?php

namespace App\Http\Middleware;

use App\Models\Country;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserCountry
{
    public function __construct(private readonly \Illuminate\Contracts\Routing\ResponseFactory $responseFactory) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $this->responseFactory->json(['message' => 'Unauthorized'], 401);
        }

        $user = $request->user();
        $country = Country::where('id', $user->country)->first();

        if ($country && $country->is_allowed == 0) {
            return $this->responseFactory->json(['message' => 'Access restricted due to country restrictions'], 403);
        }

        return $next($request);

    }
}
