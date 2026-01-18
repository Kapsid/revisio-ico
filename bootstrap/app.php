<?php

/**
 * Application Bootstrap
 *
 * Laravel 12 uses a streamlined bootstrap process.
 * This file configures routing, middleware, and exception handling.
 *
 * Key Pattern: Application Kernel
 * Instead of separate HTTP/Console kernels, Laravel 12 uses
 * a unified approach with fluent configuration methods.
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\ForceJsonResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // API middleware - force JSON responses for API routes
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom exception rendering for API
        // This ensures all API errors return consistent JSON format
    })
    ->create();
