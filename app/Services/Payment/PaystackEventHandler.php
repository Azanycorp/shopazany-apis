<?php

namespace App\Services\Payment;

use App\Enum\PaymentType;
use App\Enum\PaystackEvent;
use Illuminate\Support\Facades\Log;

class PaystackEventHandler
{
    public static function handle(array $event): void
    {
        $eventType = $event['event'];
        $data = $event['data'];

        switch ($eventType) {
            case PaystackEvent::CHARGE_SUCCESS:
                self::handleChargeSuccess($event);
                break;

            case PaystackEvent::TRANSFER_SUCCESS:
                PaystackService::handleTransferSuccess($data);
                break;

            case PaystackEvent::TRANSFER_FAILED:
                PaystackService::handleTransferFailed($data);
                break;

            case PaystackEvent::TRANSFER_REVERSED:
                PaystackService::handleTransferReversed($data);
                break;

            default:
                Log::warning("Unhandled Paystack event: {$eventType}", $data);
                break;
        }
    }

    private static function handleChargeSuccess(array $event): void
    {
        $data = $event['data'];
        $paymentType = $data['metadata']['payment_type'] ?? null;

        switch ($paymentType) {
            case PaymentType::RECURRINGCHARGE:
                PaystackService::handleRecurringCharge($event, $event['event']);
                break;

            case PaymentType::USERORDER:
                PaystackService::handlePaymentSuccess($event, $event['event']);
                break;

            case PaymentType::B2BUSERORDER:
                PaystackService::handleB2BPaymentSuccess($event, $event['event']);
                break;

            default:
                Log::warning('Unknown payment type in charge success', [
                    'payment_type' => $paymentType,
                ]);
                break;
        }
    }
}
