<?php

namespace App\Services\Curl;

use Exception;

class GetCurlService
{
    protected $baseUrl;
    protected $refrence;
    private static $secret_key;

    public function __construct($refrence)
    {
        $this->refrence = $refrence;
        $this->baseUrl = config('paystack.paymentUrl');

        if (config('services.paystack.mode') == 'live') {
            self::$secret_key = config('services.paystack.live_sk');
        } else {
            self::$secret_key = config('services.paystack.test_sk');
        }
    }

    public function run()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl . "/transaction/verify/" . $this->refrence,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer ". self::$secret_key,
            "Cache-Control: no-cache",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err !== '' && $err !== '0') {
            throw new Exception($err);
        }

        $response = json_decode($response);
        if (! $response->status) {
            throw new Exception($response->message);
        }

        return $response;
    }
}



