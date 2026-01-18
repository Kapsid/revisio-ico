<?php

/**
 * Web Routes
 *
 * These routes use session-based authentication.
 * Used for any web interface (admin panel, etc.)
 */

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This microservice is primarily API-based.
| Web routes are minimal - mainly for the welcome/info page.
|
*/

Route::get('/', function () {
    return response()->json([
        'service' => 'Registry Service',
        'version' => '1.0.0',
        'documentation' => '/api',
        'health' => '/api/health',
    ]);
});
