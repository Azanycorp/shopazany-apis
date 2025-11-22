<?php

namespace App\Services\Curl;

use Exception;
use Illuminate\Support\Facades\Http;

class PostCurl
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
                ->timeout(60)
                ->asForm()
                ->post($this->url, $this->fields);

            if ($response->failed()) {
                throw new Exception(
                    $response->json('message', 'API request failed.')
                );
            }

            $result = $response->json();

            if (! isset($result['data'])) {
                return $result;
            }

            return $result['data'];

        } catch (Exception $e) {
            throw new Exception('HTTP error: '.$e->getMessage());
        }
    }
}
