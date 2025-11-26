<?php

namespace App\Services\Curl;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChargeUserService
{
    protected string $baseUrl;

    protected object $subscription;

    private static $secret_key;

    public function __construct($subscription)
    {
        $this->subscription = $subscription;
        $this->baseUrl = config('paystack.paymentUrl');

        self::$secret_key = config('services.paystack.mode') === 'live'
            ? config('services.paystack.live_sk')
            : config('services.paystack.test_sk');
    }

    public function run()
    {
        $url = $this->baseUrl.'/transaction/charge_authorization';

        try {

            $payload = [
                'authorization_code' => $this->subscription->authorization_data?->authorization_code,
                'email' => $this->subscription->user->email,
                'amount' => $this->subscription->subscriptionPlan->cost * 100,
            ];

            $response = Http::withToken(self::$secret_key)
                ->timeout(30)
                ->asForm()
                ->post($url, $payload);

            if ($response->failed()) {
                throw new Exception($response->json('message', 'Unable to charge user.'));
            }

            $data = (object) $response->json();

            if (! $data->status) {
                throw new Exception($data->message);
            }

            return $data;
        } catch (Exception $e) {
            Log::info($e->getMessage());

            return null;
        }
    }
}
