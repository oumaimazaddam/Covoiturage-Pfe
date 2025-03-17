<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * Les middlewares globaux de l'application.
     */
    protected $middleware = [
        \Illuminate\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Http\Middleware\ValidatePostSize::class,
        
    ];

    /**
     * Les middlewares de route.
     */
    protected $routeMiddleware = [
        'role_id' => \App\Http\Middleware\RoleMiddleware::class, // Ajoutez votre middleware ici
    ];
}
