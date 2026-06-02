<?php

namespace App\Console\Commands;

use App\Enum\SubscriptionType;
use App\Models\UserSubcription;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

#[Description('Expire user subscriptions that are past 30 days of their end date.')]
#[Signature('usersubscriptions:expire')]
class ExpireUserSubscription extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        UserSubcription::where('plan_end', '<=', Date::now()->subDays(30))
            ->whereNull('expired_at')
            ->chunk(100, function ($subscriptions): void {
                foreach ($subscriptions as $subscription) {
                    $subscription->update([
                        'status' => SubscriptionType::EXPIRED,
                        'expired_at' => Date::now(),
                    ]);
                    $this->info("Expired subscription for user: {$subscription->user_id}");
                }
            });

        $this->info('Expired all subscriptions that were past 30 days.');
    }
}
