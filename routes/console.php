<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::everyMinute()
    ->withoutOverlapping()
    ->group(function () {
        // Process queued emails every minute
        Schedule::command('emails:process');
        // Queue worker: ensures jobs are processed every minute
        Schedule::command('queue:work --stop-when-empty');
    });

Schedule::daily()
    ->withoutOverlapping()
    ->group(function () {
        Schedule::command('currency:update -o');
        Schedule::command('queue:prune-batches --hours=48 --unfinished=72');
        // Product stock and pricing updates
        Schedule::command('product:check-product-stock');
        Schedule::command('app:update-product-price');
        // Expire coupons
        Schedule::command('coupon:expire');
    });

// Handle user subscriptions
Schedule::command('usersubscriptions:expire')
    ->monthlyOn(1, '00:00');

Schedule::command('usersubscriptions:charge')
    ->monthlyOn(1, '00:30')
    ->withoutOverlapping();

// Withdrawal request processing
Schedule::hourly()
    ->withoutOverlapping()
    ->group(function () {
        Schedule::command('withdraw-request:process');
        Schedule::command('b2b-payout-request');
    });
