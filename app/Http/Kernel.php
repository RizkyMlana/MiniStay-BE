<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     * Minimal untuk API Laravel.
     */
    protected $middleware = [
        // Bisa tambahkan middleware global jika perlu
    ];

    /**
     * The application's route middleware groups.
     */
    protected $middlewareGroups = [
        'api' => [
            // Minimal untuk API
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ];
}
