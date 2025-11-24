<?php

namespace App\Services\Auth;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class HttpService
{
    protected string $baseUrl;

    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.auth_service_url');
        $this->timeout = 60;
    }

    public function request(
        string $method,
        string $endpoint,
        array $data = [],
        array $headers = []
    ): Response {
        $client = Http::withHeaders(array_merge([
            'Accept' => 'application/json',
        ], $headers))
            ->timeout($this->timeout);

        return $client->{$method}($this->baseUrl.$endpoint, $data);
    }

    public function login(array $data, array $headers = []): Response
    {
        return $this->request('post', 'login', $data, $headers);
    }

    public function register(array $data, array $headers = []): Response
    {
        return $this->request('post', 'register', $data, $headers);
    }

    public function verifyCode(string $email, array $headers = []): Response
    {
        return $this->request('post', 'verify-code', ['email' => $email], $headers);
    }
}
