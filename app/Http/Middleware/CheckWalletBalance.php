<?php

namespace App\Http\Middleware;

use App\Trait\HttpResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckWalletBalance
{
    use HttpResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request?->user()?->load('wallet');

        if (!$user || !$user->wallet) {
            return $this->error(null, 'Wallet not found.', 403);
        }

        if ($user && $user?->wallet?->balance < 100.00) {
            return $this->error(null, 'Insufficient balance.', 403);
        }

        return $next($request);
    }
}
