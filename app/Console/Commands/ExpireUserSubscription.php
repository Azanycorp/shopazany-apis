<?php

namespace App\Console\Commands;

use App\Enum\SubscriptionType;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExpireUserSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usersubscriptions:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire user subscriptions that are past 30 days of their end date.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ExpireUserSubscription::where('plan_end', '<=', Carbon::now()->subDays(30))
            ->whereNull('expired_at')
            ->chunk(100, function ($subscriptions) {
                foreach ($subscriptions as $subscription) {
                    $subscription->update([
                        'status' => SubscriptionType::EXPIRED,
                        'expired_at' => Carbon::now(),
                    ]);
                    $this->info("Expired subscription for user: {$subscription->user_id}");
                }
            });

        $this->info('Expired all subscriptions that were past 30 days.');
    }
}
