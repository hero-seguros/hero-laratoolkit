<?php

namespace HeroLaraToolkit\HealthCheck\Provider;

use HeroLaraToolkit\HealthCheck\Http\Controllers\AdaptersHealthController;
use HeroLaraToolkit\HealthCheck\Http\Controllers\DatabaseHealthController;
use HeroLaraToolkit\HealthCheck\Http\Controllers\QueueHealthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class HealthCheckServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Route::get('/health-check/adapters/{name}', [AdaptersHealthController::class, 'checkAdapter']);
        Route::get('/health-check/all/adapters', [AdaptersHealthController::class, 'checkAllAdapters']);
        Route::get('/health-check/database/{connection}', [DatabaseHealthController::class, 'checkInstanceConnection']);
        Route::get('/health-check/all/databases', [DatabaseHealthController::class, 'checkAllConnections']);
        Route::get('/health-check/queue/{queue}', [QueueHealthController::class, 'checkQueue']);
        Route::get('/health-check/all/queues', [QueueHealthController::class, 'checkAllQueues']);

        $this->publishes([
            __DIR__ . '/../config/healthcheckhero.php' => config_path('healthcheckhero.php'),
        ]);
    }

    public function register()
    {
        //
    }
}
