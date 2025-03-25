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

            $success = $result['status'] === true;
            $data = $result['data'];

            foreach ($chunk as $item) {
                $this->handlePaystackResultItem(
                    $item,
                    $success,
                    $success ? null : $result,
                    $data,
                );
            }
        } catch (\Exception $e) {
            Log::error('Paystack bulk transfer exception: ' . $e);

            foreach ($chunk as $item) {
                $this->markRequestFailed($item['request_id'], $item['user_id'], $e->getMessage());
            }
        }
    }

    private function handlePaystackResultItem(array $item, bool $success, $errorMessage = null, $data = null): void
    {
        $request = WithdrawalRequest::find($item['request_id']);
        $user = User::with(['wallet'])->find($item['user_id']);

        if ($success) {
            Log::info("Bulk transfer queued: Withdrawal ID {$request->id}, Ref: {$item['reference']}");
            foreach ($data as $transfer) {
                $transferCode = $transfer['transfer_code'];
                $request->update([
                    'transfer_code' => $transferCode,
                ]);
            }
        } else {
            $this->markRequestFailed($request->id, $user->id, $errorMessage);
            $user->wallet->increment('balance', $request->amount);
            Log::error("Failed to queue Paystack bulk transfer: " . json_encode($errorMessage));
        }
    }

    private function markRequestFailed(int $requestId, int $userId, $errorMessage = null): void
    {
        $request = WithdrawalRequest::find($requestId);
        $user = User::find($userId);

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
            !isset($payload['amount'])
        ) {
            return false;
        }

        $reference = $payload['reference'];
        $amount = intval($payload['amount']);

        $request = WithdrawalRequest::where('reference', $reference)->first();

        if (!$request) {
            return false;
        }

        if (intval($request->amount * 100) !== $amount) {
            return false;
        }

        return true;
    }

}



