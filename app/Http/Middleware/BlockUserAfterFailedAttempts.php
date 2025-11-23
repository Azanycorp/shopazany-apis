<?php

namespace App\Http\Middleware;

use App\Enum\UserStatus;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockUserAfterFailedAttempts
{
    public function __construct(
        private readonly \Illuminate\Contracts\Cache\Repository $cacheManager,
        private readonly \Illuminate\Contracts\Routing\ResponseFactory $responseFactory,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Only monitor login POST requests
        if ($request->is('login') && $request->method() === 'POST') {

            $email = $request->input('email');
            $key = "failed_attempts_{$email}";

            $user = User::where('email', $email)->first();

            // Block user if already BLOCKED
            if ($user && $user->status === UserStatus::BLOCKED) {
                return $this->responseFactory->json([
                    'message' => 'Your account has been blocked due to too many failed attempts.',
                ], 403);
            }

            // Let pipeline process the login first
            $response = $next($request);

            // If pipeline login FAILED (401)
            if ($response->getStatusCode() === 401) {

                $attempts = $this->cacheManager->get($key, 0) + 1;
                $this->cacheManager->put($key, $attempts, now()->addMinutes(30));

                // 3️⃣ Block after 5 failed attempts
                if ($attempts >= 5) {
                    if ($user) {
                        $user->status = UserStatus::BLOCKED;
                        $user->save();
                    }

                    $this->cacheManager->forget($key);

                    return $this->responseFactory->json([
                        'message' => 'Your account has been blocked due to too many failed attempts.',
                    ], 403);
                }

                return $response; // Return the 401 from pipeline
            }

            // Login successful → reset attempts
            $this->cacheManager->forget($key);

            return $response;
        }

        // For non-login requests
        return $next($request);
    }
}
