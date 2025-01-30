<?php

namespace App\Services\Curl;

use Exception;

class CurrencyConversionService
{
    protected $appId;
    protected string $url;

    public function __construct()
    {
        $this->appId = config('currency.api_key');
        $this->url = "https://openexchangerates.org/api/latest.json?app_id=" . $this->appId;
    }

    public function getRates()
    {
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $json = curl_exec($ch);
        curl_close($ch);

        if (!$json) {
            throw new Exception("Failed to fetch currency rates.");
        }

        $oxr_latest = json_decode($json, true);

        return $oxr_latest['rates'];
    }
}


