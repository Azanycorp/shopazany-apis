<?php

namespace App\Services\Curl;

use Exception;
use Illuminate\Support\Facades\Log;

class ChargeUserService
{
    protected $baseUrl;

    protected $subscription;

    private static $secret_key;

    public function __construct($subscription, \Illuminate\Contracts\Config\Repository $repository, \Illuminate\Contracts\Config\Repository $repository, \Illuminate\Contracts\Config\Repository $repository, \Illuminate\Contracts\Config\Repository $repository)
    {
        $this->subscription = $subscription;
        $this->baseUrl = $repository->get('paystack.paymentUrl');

        if ($repository->get('services.paystack.mode') == 'live') {
            self::$secret_key = $repository->get('services.paystack.live_sk');
        } else {
            self::$secret_key = $repository->get('services.paystack.test_sk');
        }
    }

    public function run()
    {
        $url = $this->baseUrl.'/transaction/charge_authorization';

        try {

            $fields = [
                'authorization_code' => $this->subscription?->authorization_data?->authorization_code,
                'email' => $this->subscription?->user?->email,
                'amount' => $this->subscription?->subscriptionPlan?->cost * 100,
            ];

            $fields_string = http_build_query($fields);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer '.self::$secret_key,
                'Cache-Control: no-cache',
            ]);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            $err = curl_error($ch);

            if ($err !== '' && $err !== '0') {
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

        return null;
    }
}
