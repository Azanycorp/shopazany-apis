<?php

namespace App\Console\Commands;

use App\Enum\WithdrawalStatus;
use App\Models\User;
use App\Notifications\WithdrawalNotification;
use App\Services\PayoutService;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawRequestPayout extends Command
{
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

        User::with(['withdrawalRequest' => function ($query) {
            $query->where('status', WithdrawalStatus::PENDING)->limit(1);
        }, 'paymentMethods'])
        ->whereHas('withdrawalRequest', function ($query) {
            $query->where('status', WithdrawalStatus::PENDING);
        })
        ->chunk(100, function ($users) {
            foreach ($users as $user) {
                $this->withdraw($user);
            }
        });

        $this->info('Withdrawal request processing completed.');
    }

    private function withdraw($user)
    {
        if ($user->paymentMethods->isEmpty()) {
            $this->warn("Skipping user {$user->id}: No payment method found.");
            return;
        }

        foreach ($user->withdrawalRequest as $request) {
            if ($request->status !== WithdrawalStatus::PENDING) {
                continue;
            }
            $withdrawalAmount = $request->amount;
            if ($withdrawalAmount <= 0) {
                $this->warn("Skipping user {$user->id}, withdrawal ID {$request->id}: Invalid withdrawal amount.");
                continue;
            }
            $defaultPaymentMethod = $user->paymentMethods->where('is_default', true)->first();
            if (!$defaultPaymentMethod) {
                $this->warn("Skipping user {$user->id}: No default payment method found.");
                return;
            }
            $recipient = $defaultPaymentMethod->recipient_code;
            $fields = [
                "source" => "balance",
                "reason" => "Withdrawal",
                "amount" => intval($withdrawalAmount * 100),
                "reference" => Str::uuid(),
                "recipient" => $recipient,
            ];

            $maxRetries = 3;
            $attempt = 0;

            $this->runPayount($attempt, $maxRetries, $request, $user, $fields, $withdrawalAmount);
        }
    }

    private function runPayount($attempt, $maxRetries, $request, $user, $fields, $withdrawalAmount)
    {
        while ($attempt < $maxRetries) {
            DB::beginTransaction();
            try {
                $request->update(['status' => WithdrawalStatus::PROCESSING]);

                PayoutService::transfer($user, $fields);

                $request->update(['status' => WithdrawalStatus::COMPLETED]);
                $user->notify(new WithdrawalNotification($request, 'completed'));

                $this->info("Payout processed for user {$user->id}, withdrawal ID {$request->id} - Amount: {$withdrawalAmount}");

                DB::commit();
                return;
            } catch (\Exception $e) {
                DB::rollBack();
                $attempt++;

                if ($attempt >= $maxRetries) {
                    Log::error("Payout failed for user {$user->id}, withdrawal ID {$request->id}", ['error' => $e->getMessage()]);
                    $request->update(['status' => WithdrawalStatus::FAILED]);
                    $user->notify(new WithdrawalNotification($request, 'failed'));
                } else {
                    $this->warn("Retrying payout for user {$user->id}, withdrawal ID {$request->id} (Attempt {$attempt}/{$maxRetries})...");
                    sleep(5);
                }
            }
        }
    }
}
