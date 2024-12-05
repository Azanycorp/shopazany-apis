<?php

namespace App\Services\Curl;

use Exception;
use Illuminate\Support\Facades\Log;

class ChargeUserService
{
    protected $baseUrl;
    protected $subscription;
    private static $secret_key;

    public function __construct($subscription)
    {
        $this->subscription = $subscription;
        $this->baseUrl = config('paystack.paymentUrl');

        if (config('services.paystack.mode') == 'live') {
            self::$secret_key = config('services.paystack.live_sk');
        } else {
            self::$secret_key = config('services.paystack.test_sk');
        }
    }

    public function run()
    {
        $url = $this->baseUrl . "/transaction/charge_authorization";

        try {
            
            $fields = [
            'authorization_code' => $this->subscription?->authorization_data?->authorization_code,
            'email' => $this->subscription?->user?->email,
            'amount' => $this->subscription?->subscriptionPlan?->cost * 100,
            ];

            $fields_string = http_build_query($fields);

            $ch = curl_init();

            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POST, true);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . self::$secret_key,
            "Cache-Control: no-cache",
            ));

            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            $err = curl_error($ch);

            if ($err) {
                throw new Exception($err);
            }

            $response = json_decode($result);

            if (! $response->status) {
                throw new Exception($response->message);
            }

            return $response;

        } catch (Exception $e) {
            Log::info($e->getMessage());
        }
    }
}






