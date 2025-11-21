<?php

namespace App\Http\Middleware;

use App\Enum\UserStatus;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockUserAfterFailedAttempts
{
    public function __construct(private readonly \Illuminate\Auth\AuthManager $authManager, private readonly \Illuminate\Contracts\Cache\Repository $cacheManager, private readonly \Illuminate\Contracts\Routing\ResponseFactory $responseFactory) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $email = $request->input('email');
        $key = "failed_attempts_{$email}";

        $user = User::where('email', $email)->first();
        if ($user && $user->status === UserStatus::BLOCKED) {
            return $this->responseFactory->json(['message' => 'Your account has been blocked due to too many failed attempts.'], 403);
        }

        if (! $this->authManager->attempt($request->only('email', 'password'))) {
            $attempts = $this->cacheManager->get($key, 0) + 1;
            $this->cacheManager->put($key, $attempts, now()->addMinutes(30));

            if ($attempts >= 5) {
                if ($user) {
                    $user->status = UserStatus::BLOCKED;
                    $user->save();
                }
                $this->cacheManager->forget($key);

                return $this->responseFactory->json(['message' => 'Your account has been blocked due to too many failed attempts.'], 403);
            }

            return $this->responseFactory->json(['message' => 'Invalid credentials.'], 401);
        }

        $this->cacheManager->forget($key);

        return $next($request);
    }
}
