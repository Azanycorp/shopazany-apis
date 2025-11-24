<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $gate = $this->app->make(\Illuminate\Contracts\Auth\Access\Gate::class);
        $gate->before(function ($user, $ability): ?true {
            $permissions = $user->roles->flatMap->permissions->pluck('name')->unique();

            return $permissions->contains($ability) ? true : null;
        });
    }
}
