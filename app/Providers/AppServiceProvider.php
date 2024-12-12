<?php

namespace App\Providers;

use App\Services\AprobationService;
use App\Services\ExcelService;
use App\Services\Implementations\AprobationServiceImpl;
use App\Services\Implementations\ExcelServiceImpl;
use App\Services\Implementations\JustificationServiceImpl;
use App\Services\JustificationService;
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
        $this->app->bind('App\Services\SessionService','App\Services\Implementations\SessionServiceImpl');
        $this->app->bind('App\Services\ApprenticeService','App\Services\Implementations\ApprenticeServiceImpl');
        $this->app->bind('App\Services\CourseService','App\Services\Implementations\CourseServiceImpl');
        $this->app->bind(JustificationService::class, JustificationServiceImpl::class);
        $this->app->bind(AprobationService::class, AprobationServiceImpl::class);
        $this->app->bind(ExcelService::class, ExcelServiceImpl::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
