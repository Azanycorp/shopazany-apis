<?php

namespace App\Services\Curl;

use Exception;
use Illuminate\Support\Facades\Http;

class GetCurlService
{
    protected string $baseUrl;

    protected string $reference;

    private static $secret_key;

    public function __construct($reference)
    {
        $this->reference = $reference;
        $this->baseUrl = config('paystack.paymentUrl');

        self::$secret_key = config('services.paystack.mode') === 'live'
            ? config('services.paystack.live_sk')
            : config('services.paystack.test_sk');
    }

    public function run()
    {
        $url = $this->baseUrl.'/transaction/verify/'.$this->reference;

        $response = Http::withToken(self::$secret_key)
            ->timeout(60)
            ->get($url);

        if ($response->failed()) {
            throw new Exception($response->json('message', 'Unable to verify transaction.'));
        }

        $data = (object) $response->json();

        if (! $data->status) {
            throw new Exception($data->message);
        }

        return $data;
    }
}
