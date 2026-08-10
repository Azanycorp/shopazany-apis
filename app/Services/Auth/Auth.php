<?php

namespace App\Services\Auth;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class Auth
{
    public function __construct(private readonly Repository $repository) {}

    public function request(string $method, string $endpoint, ?array $data = [], ?string $token = null)
    {
        $client = Http::withHeaders([
            $this->repository->get('security.auth_header_key') => $this->repository->get('security.auth_header_value'),
        ]);

        if ($token) {
            $client = $client->withToken($token);
        }

        return match (strtolower($method)) {
            'get' => $client->get($endpoint, $data),
            'post' => $client->post($endpoint, $data),
            'put' => $client->put($endpoint, $data),
            'patch' => $client->patch($endpoint, $data),
            'delete' => $client->delete($endpoint, $data),
            default => throw new \InvalidArgumentException("Unsupported method [$method]"),
        };
    }

    public function sendRequest(string $method, string $endpoint, RequestOptions|array|null $options = null)
    {
        $options = is_array($options) ? new RequestOptions(data: $options) : ($options ?? new RequestOptions);
        $method = strtolower($method);

        $headers = array_merge(
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            $this->getAuthHeaders($method, $endpoint, $options),
            $options->getHeaders()
        );

        try {
            $client = Http::withHeaders($headers)
                ->timeout($options->getTimeout())
                ->connectTimeout($options->getConnectTimeout())
                ->retry(
                    $options->getRetries(),
                    $options->getRetryDelay(),
                    function (Throwable $exception): bool {
                        return $this->shouldRetry($exception);
                    }
                );

            if ($options->getToken()) {
                $client = $client->withToken($options->getToken());
            }

            return match (strtolower($method)) {
                'get' => $client->get($endpoint, $options->getData()),
                'post' => $client->post($endpoint, $options->getData()),
                'put' => $client->put($endpoint, $options->getData()),
                'patch' => $client->patch($endpoint, $options->getData()),
                'delete' => $client->delete($endpoint, $options->getData()),
                default => throw new \InvalidArgumentException("Unsupported method [$method]"),
            };

        } catch (Throwable $e) {
            logger()->error("HTTP Request Timeout: {$method} {$endpoint}", [
                'error' => $e->getMessage(),
                'timeout' => $options->getTimeout(),
            ]);

            return [
                'status' => false,
                'message' => "An error occured: {$e->getMessage()}, Timeout: {$options->getTimeout()}",
                'data' => null,
            ];
        }
    }

    private function shouldRetry(Throwable $exception): bool
    {
        if ($exception instanceof ConnectionException) {
            return true;
        }

        $code = $exception->getCode();

        if ($code === 504) {
            return false;
        }

        return $code >= 500;
    }

    public function get(string $endpoint, RequestOptions|array|null $options = null)
    {
        return $this->sendRequest('GET', $endpoint, $options);
    }

    public function post(string $endpoint, RequestOptions|array|null $options = null)
    {
        return $this->sendRequest('POST', $endpoint, $options);
    }

    public function patch(string $endpoint, RequestOptions|array|null $options = null)
    {
        return $this->sendRequest('PATCH', $endpoint, $options);
    }

    public function put(string $endpoint, RequestOptions|array|null $options = null)
    {
        return $this->sendRequest('PUT', $endpoint, $options);
    }

    public function delete(string $endpoint, RequestOptions|array|null $options = null)
    {
        return $this->sendRequest('DELETE', $endpoint, $options);
    }

    public function isSuccessful(Response $response): bool
    {
        return $response->successful();
    }

    private function getBody(RequestOptions|array|null $options, string $method, string $path, string $timestamp)
    {
        $body = json_encode($options->getData());

        return implode("\n", [
            $timestamp,
            strtoupper($method),
            $path,
            $body,
        ]);
    }

    private function getAuthHeaders(string $method, string $endpoint, RequestOptions $options): array
    {
        $apiKey = config('services.payment_service.api_key');

        if (! in_array($method, ['post', 'put', 'patch'], true)) {
            return [
                'X-API-KEY' => $apiKey,
            ];
        }

        $timestamp = (string) time();
        $body = $this->getBody($options, $method, $endpoint, $timestamp);
        $secret = config('services.payment_service.api_secret');

        return [
            'X-API-KEY' => $apiKey,
            'X-TIMESTAMP' => $timestamp,
            'X-NONCE' => (string) Str::uuid(),
            'X-SIGNATURE' => hash_hmac('sha256', $body, $secret),
        ];
    }
}
