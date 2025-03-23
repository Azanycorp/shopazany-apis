<?php

namespace App\Console\Commands;

use App\Enum\WithdrawalStatus;
use App\Models\User;
use App\Notifications\WithdrawalNotification;
use App\Services\PayoutService;
use App\Trait\Transfer;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawRequestPayout extends Command
{
    use Transfer;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'withdraw-request:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Payout system for withdrawal requests';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing pending withdrawal requests...');

        User::with(['withdrawalRequests' => function ($query) {
            $query->where('status', WithdrawalStatus::PENDING)->limit(1);
        }, 'paymentMethods'])
        ->whereHas('withdrawalRequests', function ($query) {
            $query->where('status', WithdrawalStatus::PENDING);
        })
        ->chunk(100, function ($users) {
            foreach ($users as $user) {
                $this->withdraw($user);
            }
        });

        $this->processPaystackBulkTransfers();

        $this->info('Withdrawal request processing completed.');
    }

    private function withdraw($user)
    {
        if ($user->paymentMethods->isEmpty()) {
            $this->warn("Skipping user {$user->id}: No payment method found.");
            return;
        }

        $defaultPaymentMethod = $user->paymentMethods->where('is_default', true)->first();
        if (!$defaultPaymentMethod) {
            $this->warn("Skipping user {$user->id}: No default payment method found.");
            return;
        }

        $platform = $defaultPaymentMethod->platform;

        if ($platform === 'paystack') {
            return;
        }

        foreach ($user->withdrawalRequests as $request) {
            if ($request->status !== WithdrawalStatus::PENDING) {
                continue;
            }

            $withdrawalAmount = $request->amount;
            if ($withdrawalAmount <= 0) {
                $this->warn("Skipping user {$user->id}, withdrawal ID {$request->id}: Invalid withdrawal amount.");
                continue;
            }

            $request->update([
                'status' => WithdrawalStatus::PROCESSING,
            ]);

            $data = [
                'platform' => $platform,
                'data' => $defaultPaymentMethod,
            ];

            $maxRetries = 3;
            $attempt = 0;

            $this->runPayount($attempt, $maxRetries, $request, $user, $withdrawalAmount, $data);
        }
    }

    private function runPayount($attempt, $maxRetries, $request, $user, $withdrawalAmount, $data)
    {
        while ($attempt < $maxRetries) {
            DB::beginTransaction();
            try {
                $res = $this->executePayout($data, $request, $user);

                if (!$res['status']) {
                    throw new \Exception("{$data['platform']} transfer failed: " . $res['message']);
                }

                if ($data['platform'] === 'authorize') {
                    $request->update(['status' => WithdrawalStatus::COMPLETED]);
                    $user->notify(new WithdrawalNotification($request, 'completed'));
                    Log::info("Authorize.Net Transfer Completed: User ID {$user->id}, Withdrawal ID {$request->id}");
                } else {
                    Log::info("Withdrawal initiated: User ID {$user->id}, Withdrawal ID {$request->id}");
                }

                $this->info("Payout processed for user {$user->id}, withdrawal ID {$request->id} - Amount: {$withdrawalAmount}");

                DB::commit();
                return;
            } catch (\Exception $e) {
                DB::rollBack();
                $attempt++;

                $this->handlePayoutFailure($request, $user, $e, ++$attempt, $maxRetries);
            }
        }
    }

    private function executePayout($data, $request, $user)
    {
        if ($data['platform'] === 'authorize') {
            return PayoutService::authorizeTransfer($request, $user, $data['data']);
        } else {
            throw new \Exception("Unsupported payment platform: {$data['platform']}");
        }
    }

    private function handlePayoutFailure($request, $user, $exception, $attempt, $maxRetries)
    {
        if ($attempt >= $maxRetries) {
            Log::error("Payout failed for user {$user->id}, withdrawal ID {$request->id}", ['error' => $exception->getMessage()]);
            $request->update(['status' => WithdrawalStatus::FAILED]);
            $user->notify(new WithdrawalNotification($request, 'failed'));
        } else {
            $this->warn("Retrying payout for user {$user->id}, withdrawal ID {$request->id} (Attempt {$attempt}/{$maxRetries})...");
            sleep(5);
        }
    }

    private function processPaystackBulkTransfers()
    {
        $this->info('Processing Paystack bulk withdrawals...');

        $paystackRequests = $this->collectPaystackRequests();

        if (empty($paystackRequests)) {
            $this->info('No Paystack withdrawals to process.');
            return;
        }

        foreach (array_chunk($paystackRequests, 100) as $chunk) {
            $this->handlePaystackChunk($chunk);
        }

        $this->info('Paystack bulk withdrawal processing done.');
    }

}
