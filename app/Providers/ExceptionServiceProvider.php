<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Request;
use App\Services\ExceptionHandlingService;
use App\Services\BugAtlasReporterService;

class ExceptionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->bind('Illuminate\Contracts\Debug\ExceptionHandler', function ($app) {
            return new ExceptionHandlingService($app, new BugAtlasReporterService());
        });
    }
}
