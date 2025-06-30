<?php

namespace App\Console\Commands;

use App\Enum\PaymentType;
use App\Enum\PaystackEvent;
use App\Enum\SubscriptionType;
use App\Models\PaymentLog;
use App\Models\User;
use App\Models\UserSubcription;
use App\Services\Curl\ChargeUserService;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChargeUserSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usersubscriptions:charge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Charge users for subscriptions that have expired or are due for renewal.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        UserSubcription::where('plan_end', '<=', Carbon::now()->subDays(30))
            ->where('subscription_type', PaymentType::PAYSTACK)
            ->whereNotNull('authorization_data')
            ->whereNull('expired_at')
            ->chunk(100, function ($subscriptions): void {
                foreach ($subscriptions as $subscription) {
                    $this->chargeSubscription($subscription);
                }
            });

        $this->info('Subscriptions charged successfully.');
    }

    private function chargeSubscription($subscription): void
    {
        DB::beginTransaction();

        try {
            $response = (new ChargeUserService($subscription))->run();

            if ($response['status'] === true) {

                $user = User::with([
                    'referrer' => function ($query) {
                        $query->with('wallet');
                    },
                    'userSubscription',
                ])
                    ->findOrFail($subscription->user_id);

                $method = $response['metadata']['payment_method'];
                $authData = $response['authorization'];
                $amount = $response['amount'];
                $referrer = User::with(['wallet'])->findOrFail($user->referrer->first()->id);

                $payment = $user->payments()->create([
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone_number' => $user->phone,
                    'amount' => number_format($amount / 100, 2, '.', ''),
                    'reference' => $response['reference'],
                    'channel' => $response['channel'],
                    'currency' => $response['currency'],
                    'ip_address' => $response['ip_address'],
                    'paid_at' => $response['transaction_date'],
                    'createdAt' => $response['transaction_date'],
                    'transaction_date' => $response['transaction_date'],
                    'status' => $response['status'],
                    'type' => PaymentType::RECURRINGCHARGE,
                ]);

                PaymentLog::create([
                    'payment_id' => $payment->id,
                    'data' => $response,
                    'method' => $method,
                    'status' => PaystackEvent::CHARGE_SUCCESS,
                    'type' => PaymentType::RECURRINGCHARGE,
                ]);

                $subscription->update(['status' => SubscriptionType::EXPIRED]);

                $user->userSubscription()->create([
                    'subscription_plan_id' => $subscription->subscription_plan_id,
                    'payment_id' => $payment->id,
                    'plan_start' => now(),
                    'plan_end' => now()->addDays(30),
                    'subscription_type' => PaymentType::PAYSTACK,
                    'authorization_data' => $authData,
                    'status' => SubscriptionType::ACTIVE,
                    'expired_at' => null,
                ]);

                SubscriptionService::creditAffiliate($referrer, $amount, $user);

                Log::info('Subscription charged successfully: '.$subscription->id);
                DB::commit();
            } else {
                Log::error('Failed to charge subscription: '.$response['message']);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error charging subscription: '.$e->getMessage());
        }
    }
}
