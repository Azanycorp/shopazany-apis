<?php

namespace App\Services\Curl;

use Illuminate\Support\Facades\Http;

class CurlService
{
    public function __construct(
        protected string $url,
        protected array $headers = [],
        protected array $fields = []
    ) {}

    public function execute()
    {
        try {
            $response = Http::withHeaders($this->headers)
                ->timeout(30)
                ->asForm()
                ->post($this->url, $this->fields);

            if ($response->failed()) {
                return [
                    'status' => false,
                    'message' => $response->json('message', 'Request failed'),
                    'data' => null,
                ];
            }

            return $response->json();

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ];
        }
    }
}
