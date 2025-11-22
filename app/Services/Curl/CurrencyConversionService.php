<?php

namespace App\Services\Curl;

use Exception;
use Illuminate\Support\Facades\Http;

class CurrencyConversionService
{
    protected $appId;

    protected string $url;

    public function __construct()
    {
        $this->appId = config('currency.api_key');
        $this->url = 'https://openexchangerates.org/api/latest.json?app_id='.$this->appId;
    }

    public function getRates()
    {
        $response = Http::timeout(60)->get($this->url);

        if ($response->failed()) {
            throw new Exception('Failed to fetch currency rates.');
        }

        $json = $response->json();

        if (! isset($json['rates'])) {
            throw new Exception('Invalid response: rates not found.');
        }

        return $json['rates'];
    }
}
