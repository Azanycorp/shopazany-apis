<?php

use App\Http\Middleware\AuthGates;
use App\Http\Middleware\B2BBuyer;
use App\Http\Middleware\B2BSeller;
use App\Http\Middleware\BlockUserAfterFailedAttempts;
use App\Http\Middleware\BuyerAuthMiddleware;
use App\Http\Middleware\CheckWalletBalance;
use App\Http\Middleware\SellerAuthMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api/admin')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('auth-gates', [
            AuthGates::class,
        ]);

        $middleware->alias([
            'check.wallet' => CheckWalletBalance::class,
            'buyer.auth' => BuyerAuthMiddleware::class,
            'seller.auth' => SellerAuthMiddleware::class,
            'b2b_seller.auth' => B2BSeller::class,
            'b2b_buyer.auth' => B2BBuyer::class,
            'login.attempt' => BlockUserAfterFailedAttempts::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (Throwable $e) {
            Log::channel('slack')->error($e->getMessage(),[
                'file' => $e->getFile(),
                'Line' => $e->getLine(),
                'code' => $e->getCode(),
            ]);
        });
    })->create();
