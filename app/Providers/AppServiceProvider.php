<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use App\Observers\UserObserver;
use App\Observers\OrderObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Laravel\Sanctum\PersonalAccessToken;
use App\Contracts\B2BRepositoryInterface;
use Illuminate\Validation\Rules\Password;
use App\Repositories\B2BProductRepository;
use Illuminate\Support\Facades\RateLimiter;
use App\Repositories\B2BSellerShippingRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(B2BRepositoryInterface::class, B2BProductRepository::class);
        $this->app->bind(B2BRepositoryInterface::class, B2BSellerShippingRepository::class);
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

        $this->configureCommands();
        $this->configureModels();
        $this->configureUrl();
    }

    /**
     * Configure the application's command.
     */
    private function configureCommands(): void
    {
        DB::prohibitDestructiveCommands(
            $this->app->isProduction(),
        );
    }

    /**
     * Configure the application's models.
     */
    private function configureModels(): void
    {
        Model::shouldBeStrict();
        Model::unguard();
        Model::automaticallyEagerLoadRelationships();
    }

    /**
     * Configure the application's URL.
     */
    private function configureUrl(): void
    {
        URL::formatScheme('https');
    }
}
