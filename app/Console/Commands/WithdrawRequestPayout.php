<?php

namespace App\Console\Commands;

use App\Enum\WithdrawalStatus;
use App\Models\User;
use App\Notifications\WithdrawalNotification;
use App\Services\PayoutService;
use App\Trait\Transfer;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
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
     * Create a new console command instance.
     */
    public function __construct(private readonly \Illuminate\Database\DatabaseManager $databaseManager)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Processing pending withdrawal requests...');

        User::with(['withdrawalRequests' => function ($query): void {
            $query->where('status', WithdrawalStatus::PENDING)->limit(1);
        }, 'paymentMethods'])
            ->whereHas('withdrawalRequests', function (Builder $query): void {
                $query->where('status', WithdrawalStatus::PENDING);
            })
            ->chunk(100, function ($users): void {
                foreach ($users as $user) {
                    $this->withdraw($user);
                }
            });

        $this->processPaystackBulkTransfers();

        $this->info('Withdrawal request processing completed.');
    }

    private function withdraw(User $user): void
    {
        if ($user->paymentMethods->isEmpty()) {
            $this->warn("Skipping user {$user->id}: No payment method found.");

            return;
        }

        $defaultPaymentMethod = $user->paymentMethods->where('is_default', true)->first();
        if (! $defaultPaymentMethod) {
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

    private function runPayount(int $attempt, int $maxRetries, $request, $user, $withdrawalAmount, array $data): void
    {
        while ($attempt < $maxRetries) {
            $this->databaseManager->beginTransaction();
            try {
                $res = $this->executePayout($data, $request, $user);

                if (! $res['status']) {
                    throw new \Exception("{$data['platform']} transfer failed: ".$res['message']);
                }

                if ($data['platform'] === 'authorize') {
                    $request->update(['status' => WithdrawalStatus::COMPLETED]);
                    $user->notify(new WithdrawalNotification($request, 'completed'));
                    Log::info("Authorize.Net Transfer Completed: User ID {$user->id}, Withdrawal ID {$request->id}");
                } else {
                    Log::info("Withdrawal initiated: User ID {$user->id}, Withdrawal ID {$request->id}");
                }

                $this->info("Payout processed for user {$user->id}, withdrawal ID {$request->id} - Amount: {$withdrawalAmount}");

                $this->databaseManager->commit();

                return;
            } catch (\Exception $e) {
                $this->databaseManager->rollBack();
                $attempt++;

                $this->handlePayoutFailure($request, $user, $e, ++$attempt, $maxRetries);
            }
        }
    }

    private function executePayout(array $data, $request, $user)
    {
        if ($data['platform'] === 'authorize') {
            return PayoutService::authorizeTransfer($request, $user, $data['data']);
        }
        throw new \Exception("Unsupported payment platform: {$data['platform']}");
    }

    private function handlePayoutFailure($request, $user, \Exception $exception, int|float $attempt, $maxRetries): void
    {
        if ($attempt >= $maxRetries) {
            Log::error("Payout failed for user {$user->id}, withdrawal ID {$request->id}", ['error' => $exception->getMessage()]);
            $request->update(['status' => WithdrawalStatus::FAILED]);
            $user->notify(new WithdrawalNotification($request, 'failed'));
        } else {
            $this->warn("Retrying payout for user {$user->id}, withdrawal ID {$request->id} (Attempt {$attempt}/{$maxRetries})...");
            \Illuminate\Support\Sleep::sleep(5);
        }
    }

    private function processPaystackBulkTransfers(): void
    {
        $this->info('Processing Paystack bulk withdrawals...');

        $paystackRequests = $this->collectPaystackRequests();

        if ($paystackRequests === []) {
            $this->info('No Paystack withdrawals to process.');

            return;
        }

        foreach (array_chunk($paystackRequests, 100) as $chunk) {
            $this->handlePaystackChunk($chunk);
        }

        $this->info('Paystack bulk withdrawal processing done.');
    }
}
