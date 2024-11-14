<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('currency:update -o')->daily();
Schedule::command('queue:work --stop-when-empty')->everyMinute();
Schedule::command('queue:prune-batches --hours=48 --unfinished=72')->daily();
Schedule::command('usersubscriptions:expire')->monthlyOn(1, '00:00');
Schedule::command('usersubscriptions:charge')->monthlyOn(1, '00:30');
Schedule::command('product:check-product-stock')->daily();
Schedule::command('app:update-product-price')->daily();

