<?php

namespace App\Http\Middleware;

use App\Models\Country;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserCountry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = $request->user();
        $country = Country::where('id', $user->country)->first();

        if ($country && $country->is_allowed == 0) {
            return response()->json(['message' => 'Access restricted due to country restrictions'], 403);
        }

        return $next($request);

    }
}
