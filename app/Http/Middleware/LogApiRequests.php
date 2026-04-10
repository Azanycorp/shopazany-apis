<?php

namespace App\Http\Middleware;

use App\Jobs\LogApiRequestJob;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    // Headers you don't want stored (sensitive data)
    protected array $excludedHeaders = ['authorization', 'cookie', 'set-cookie', 'x-shpazy-auth'];

    // Request fields to mask
    protected array $sensitiveFields = ['password', 'password_confirmation', 'token', 'secret'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = (microtime(true) - $startTime) * 1000;

        // Log asynchronously to avoid slowing down the response
        $this->logRequest($request, $response, $duration);

        return $response;
    }

    protected function logRequest(Request $request, Response $response, float $duration): void
    {
        try {
            $payload = $request->except($this->sensitiveFields);

            $headers = (new Collection($request->headers->all()))
                ->except($this->excludedHeaders)
                ->all();

            $responseBody = null;
            $content = $response->getContent();
            if (strlen($content) < 10000) {
                $decoded = json_decode($content, true);
                $responseBody = json_last_error() === JSON_ERROR_NONE ? $decoded : $content;
            }

            $logData = [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => $request->route()?->getName(),
                'headers' => $headers,
                'payload' => $payload,
                'response' => $responseBody,
                'status_code' => $response->getStatusCode(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => userAuthId(),
                'duration_ms' => round($duration, 2),
            ];

            dispatch(new LogApiRequestJob($logData));
        } catch (\Throwable $e) {
            logger()->error('Request logging failed: '.$e->getMessage());
        }
    }
}
