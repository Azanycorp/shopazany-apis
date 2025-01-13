<?php

namespace App\Http\Middleware;

use App\Enum\UserStatus;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class BlockUserAfterFailedAttempts
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $email = $request->input('email');
        $key = 'failed_attempts_' . $email;

        $user = User::where('email', $email)->first();
        if ($user && $user->status === UserStatus::BLOCKED) {
            return response()->json(['message' => 'Your account has been blocked due to too many failed attempts.'], 403);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            $attempts = Cache::get($key, 0) + 1;
            Cache::put($key, $attempts, now()->addMinutes(30));

            if ($attempts >= 5) {
                if ($user) {
                    $user->status = UserStatus::BLOCKED;
                    $user->save();
                }
                Cache::forget($key);
                return response()->json(['message' => 'Your account has been blocked due to too many failed attempts.'], 403);
            }

            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        Cache::forget($key);

        return $next($request);
    }
}
