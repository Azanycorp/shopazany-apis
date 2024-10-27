<?php

namespace App\Providers;

use App\Contracts\B2BRepositoryInterface;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use App\Observers\UserObserver;
use App\Observers\OrderObserver;
use App\Repositories\B2BRepository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(B2BRepositoryInterface::class, B2BRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        Password::defaults(function () {
            return Password::min(8)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised();
        });

        RateLimiter::for('apis', function (Request $request) {
            return $request->user() ?
                Limit::perMinute(60)->by($request->ip())
                : Limit::perMinute(20)->by($request->ip());
        });

        User::observe(UserObserver::class);
        Order::observe(OrderObserver::class);
    }
}
