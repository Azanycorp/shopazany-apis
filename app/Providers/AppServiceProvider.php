<?php

namespace App\Providers;

use App\Contracts\B2BRepositoryInterface;
use App\Models\Order;
use App\Models\User;
use App\Observers\OrderObserver;
use App\Observers\UserObserver;
use App\Repositories\B2BProductRepository;
use App\Repositories\B2BSellerShippingRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

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
        $databaseManager = $this->app->make(\Illuminate\Database\DatabaseManager::class);
        $databaseManager->prohibitDestructiveCommands($this->app->isProduction());
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
        $urlGenerator = $this->app->make(\Illuminate\Routing\UrlGenerator::class);
        $urlGenerator->formatScheme('https');
    }
}
