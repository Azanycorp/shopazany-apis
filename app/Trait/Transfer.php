<?php

namespace App\Trait;

use App\Models\User;
use Illuminate\Support\Str;
use App\Enum\WithdrawalStatus;
use App\Models\WithdrawalRequest;
use App\Notifications\WithdrawalNotification;
use App\Services\PayoutService;
use Illuminate\Support\Facades\Log;

trait Transfer
{
    protected function collectPaystackRequests(): array
    {
        $requests = [];

        User::with(['withdrawalRequests' => function ($query) {
            $query->where('status', WithdrawalStatus::PENDING);
        }, 'paymentMethods'])
        ->whereHas('withdrawalRequests', function ($query) {
            $query->where('status', WithdrawalStatus::PENDING);
        })
        ->chunk(100, function ($users) use (&$requests) {
            foreach ($users as $user) {
                $this->extractUserPaystackRequests($user, $requests);
            }
        });

        return $requests;
    }

    protected function extractUserPaystackRequests($user, array &$requests): void
    {
        $paymentMethod = $user->paymentMethods->where('is_default', true)->first();

        if (!$paymentMethod || $paymentMethod->platform !== 'paystack') {
            return;
        }

        foreach ($user->withdrawalRequests as $request) {
            if ($request->status !== WithdrawalStatus::PENDING) {
                continue;
            }

            $amount = intval($request->amount * 100);
            if ($amount <= 0 || !$paymentMethod->recipient_code) {
                continue;
            }

            $reference = (string) Str::uuid();

            $request->update([
                'status' => WithdrawalStatus::PROCESSING,
                'reference' => $reference,
            ]);

            $requests[] = [
                'reference' => $reference,
                'amount' => $amount,
                'recipient' => $paymentMethod->recipient_code,
                'reason' => 'Withdrawal',
                'request_id' => $request->id,
                'user_id' => $user->id,
            ];
        }
    }

    protected function handlePaystackChunk(array $chunk): void
    {
        try {
            $result = PayoutService::paystackBulkTransfer($chunk);

            foreach ($chunk as $item) {
                $this->handlePaystackResultItem($item, $result['status'], $result['message'] ?? null);
            }
        } catch (\Exception $e) {
            Log::error('Paystack bulk transfer exception: ' . $e->getMessage());

            foreach ($chunk as $item) {
                $this->markRequestFailed($item['request_id'], $item['user_id'], $e->getMessage());
            }
        }
    }

    private function handlePaystackResultItem(array $item, bool $success, ?string $errorMessage = null): void
    {
        $request = WithdrawalRequest::find($item['request_id']);
        $user = User::find($item['user_id']);

        if ($success) {
            Log::info("Bulk transfer queued: Withdrawal ID {$request->id}, Ref: {$item['reference']}");
        } else {
            $this->markRequestFailed($request->id, $user->id, $errorMessage);
            Log::error("Failed to queue Paystack bulk transfer: {$errorMessage}");
        }
    }

    private function markRequestFailed(int $requestId, int $userId, $errorMessage = null): void
    {
        $request = WithdrawalRequest::find($requestId);
        $user = User::with(['wallet'])->find($userId);

        $user->wallet->increment('balance', $request->amount);

        $request->update([
            'status' => WithdrawalStatus::FAILED,
            'response' => $errorMessage,
        ]);

        $user->notify(new WithdrawalNotification($request, 'failed'));
    }

    protected function isValidTransferRequest(array $payload): bool
    {
        if (
            !isset($payload['reference']) ||
            !isset($payload['amount']) ||
            !isset($payload['recipient'])
        ) {
            return false;
        }

        $reference = $payload['reference'];
        $amount = $payload['amount'];
        $recipient = $payload['recipient'];

        $request = WithdrawalRequest::where('reference', $reference)->first();

        if (!$request) {
            return false;
        }

        if (
            intval($request->amount * 100) !== intval($amount)
        ) {
            return false;
        }

        $paymentMethod = $request->user->paymentMethods()->where('is_default', true)->first();
        if (!$paymentMethod || $paymentMethod->recipient_code !== $recipient) {
            return false;
        }

        return true;
    }
}



