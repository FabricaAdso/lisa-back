<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('App\Services\AuthService','App\Services\Implementations\AuthServiceImpl');
        $this->app->bind('App\Services\RoleService','App\Services\Implementations\RoleServiceImpl');
        $this->app->bind('App\Services\TokenService','App\Services\Implementations\TokenServiceImpl');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
