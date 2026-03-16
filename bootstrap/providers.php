<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use Jenssegers\Agent\AgentServiceProvider;
use Maatwebsite\Excel\ExcelServiceProvider;
use Torann\Currency\CurrencyServiceProvider;
use Unicodeveloper\Paystack\PaystackServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    AgentServiceProvider::class,
    ExcelServiceProvider::class,
    CurrencyServiceProvider::class,
    PaystackServiceProvider::class,
];
