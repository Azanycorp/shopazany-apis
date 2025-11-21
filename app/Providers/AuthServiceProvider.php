<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Create a new service provider instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     */
    public function __construct($app, private readonly \Illuminate\Contracts\Auth\Access\Gate $gate)
    {
        parent::__construct($app);
    }

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
        $this->gate->before(function ($user, $ability): ?true {
            $permissions = $user->roles->flatMap->permissions->pluck('name')->unique();

            return $permissions->contains($ability) ? true : null;
        });
    }
}
