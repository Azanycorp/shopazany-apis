<?php

namespace App\Services\Curl;

use Exception;
use Illuminate\Support\Facades\Http;

class GetCurl
{
    public function __construct(
        protected string $url,
        protected array $headers = []
    ) {}

    public function execute(): array
    {
        $response = Http::withHeaders($this->headers)
            ->timeout(30)
            ->get($this->url);

        if ($response->failed()) {
            throw new Exception('Request error: '.$response->json('message', 'Unknown error'));
        }

        $result = $response->json();

        if (! $result || ! isset($result['data'])) {
            return [
                'status' => false,
                'message' => $result['message'] ?? 'Invalid response format',
            ];
        }

        return $result['data'];
    }
}
