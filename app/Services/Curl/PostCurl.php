<?php

namespace App\Services\Curl;

use Exception;

class PostCurl
{
    protected $url;
    protected $headers = [];
    protected $fields;

    public function __construct(string $url, array $headers = [], array $fields = [])
    {
        $this->url = $url;
        $defaultHeaders = [
            'Content-Type' => 'application/json',
        ];

        $allHeaders = array_merge($defaultHeaders, $headers);
        foreach ($allHeaders as $key => $value) {
            $this->headers[] = "$key: $value";
        }
        $this->fields = $fields;
    }

    public function execute()
    {
        $fields_string = json_encode($this->fields);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL error: $error_msg");
        }

        $result = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from API: ' . json_last_error_msg());
        }

        return $result;
    }
}
