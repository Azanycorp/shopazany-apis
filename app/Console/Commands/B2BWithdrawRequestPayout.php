<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Support\Str;
use App\Enum\WithdrawalStatus;
use App\Services\PayoutService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\WithdrawalNotification;

class B2BWithdrawRequestPayout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'b2b-payout-request';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Payout scheduler for b2b seller\'s withdrawal requests';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing pending withdrawal requests...');

        User::with(['payout' => function ($query) {
            $query->where('status', WithdrawalStatus::PENDING)->limit(1);
        }, 'B2bWithdrawalMethod'])
        ->whereHas('payout', function ($query) {
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
        if ($user->B2bWithdrawalMethod->isEmpty()) {
            $this->warn("Skipping user {$user->id}: No payment method found.");
            return;
        }

        foreach ($user->payout as $request) {
            if ($request->status !== WithdrawalStatus::PENDING) {
                continue;
            }
            $withdrawalAmount = $request->amount;
            if ($withdrawalAmount <= 0) {
                $this->warn("Skipping user {$user->id}, withdrawal ID {$request->id}: Invalid withdrawal amount.");
                continue;
            }
            $defaultPaymentMethod = $user->B2bWithdrawalMethod->where('is_default',1)->first();
            if (!$defaultPaymentMethod) {
                $this->warn("Skipping user {$user->id}: No default payment method found.");
                return;
            }

            $platform = $defaultPaymentMethod->platform;
            $recipient = $defaultPaymentMethod->recipient_code;
            $reference = Str::uuid();

            $fields = [
                "source" => "balance",
                "reason" => "Withdrawal",
                "amount" => intval($withdrawalAmount * 100),
                "reference" => $reference,
                "recipient" => $recipient,
            ];

            if ($platform === 'paystack') {
                $fields['reference'] = $reference;
                $request->update([
                    'status' => WithdrawalStatus::PROCESSING,
                    'reference' => $reference,
                ]);
            } else {
                $request->update([
                    'status' => WithdrawalStatus::PROCESSING,
                ]);
            }

            $data = [
                'platform' => $platform,
                'data' => $defaultPaymentMethod,
            ];

            $maxRetries = 3;
            $attempt = 0;

            $this->runPayount($attempt, $maxRetries, $request, $user, $fields, $withdrawalAmount, $data);
        }
    }

    private function runPayount($attempt, $maxRetries, $request, $user, $fields, $withdrawalAmount, $data)
    {
        while ($attempt < $maxRetries) {
            DB::beginTransaction();
            try {
                $res = $this->executePayout($data, $request, $user, $fields);

                if (!$res['status']) {
                    throw new \Exception("{$data['platform']} transfer failed: " . $res['message']);
                }

                if ($data['platform'] === 'authorize') {
                    $request->update(['status' => WithdrawalStatus::COMPLETED]);
                    $user->notify(new WithdrawalNotification($request, 'completed'));
                    Log::info("Authorize.Net Transfer Completed: User ID {$user->id}, Withdrawal ID {$request->id}");
                } else {
                    Log::info("Withdrawal initiated: User ID {$user->id}, Withdrawal ID {$request->id}, Reference {$fields['reference']}");
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

    private function executePayout($data, $request, $user, $fields)
    {
        if ($data['platform'] === 'paystack') {
            return PayoutService::paystackTransfer($request, $user, $fields);
        } elseif ($data['platform'] === 'authorize') {
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
}
