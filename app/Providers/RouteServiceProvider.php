<?php

namespace App\Providers;


use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';
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
        // API routes
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));

            // Web routes
        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }
}
