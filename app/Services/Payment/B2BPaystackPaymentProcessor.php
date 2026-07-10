<?php

namespace App\Services\Payment;

use App\Contracts\PaymentStrategy;
use App\Services\Auth\Auth;
use App\Services\Auth\RequestOptions;
use App\Trait\HttpResponse;

class B2BPaystackPaymentProcessor implements PaymentStrategy
{
    use HttpResponse;

    public function processPayment(array $paymentDetails): array
    {
        $url = config('services.payment_service.url').'/paystack/initialize';
        $service = resolve(Auth::class);

        try {
            $response = $service->post(
                $url,
                new RequestOptions(
                    data: ['data' => $paymentDetails],
                )
            );

            if ($response->failed()) {
                return [
                    'status' => false,
                    'message' => $response['message'] ?? 'Failed to initialize payment',
                    'data' => null,
                ];
            }

            $data = $response->json();

            return [
                'status' => 'success',
                'data' => $data['data'],
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ];
        }
    }
}
