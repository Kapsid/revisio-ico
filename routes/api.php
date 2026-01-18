<?php

/**
 * API Routes
 *
 * All routes defined here are prefixed with /api
 * and use the 'api' middleware group.
 *
 * Route Design:
 * - RESTful resource naming
 * - Version prefix can be added later (/api/v1/...)
 * - Clear, predictable URL structure
 */

use App\Http\Controllers\Api\CompanyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Company Information Routes
|--------------------------------------------------------------------------
|
| These routes provide access to company registry data.
| Currently not protected - AWS ECS handles IP restrictions.
| Authentication can be added later if needed.
|
*/

Route::prefix('company')->group(function () {
    // GET /api/company/info/{countryCode}/{companyId}
    // Main endpoint - returns company information
    Route::get('info/{countryCode}/{companyId}', [CompanyController::class, 'show'])
        ->name('company.info')
        ->where('countryCode', 'cz|sk|pl')
        ->where('companyId', '[0-9]+');

    // POST /api/company/refresh/{countryCode}/{companyId}
    // Force refresh from registry (bypasses cache)
    Route::post('refresh/{countryCode}/{companyId}', [CompanyController::class, 'refresh'])
        ->name('company.refresh')
        ->where('countryCode', 'cz|sk|pl')
        ->where('companyId', '[0-9]+');
});

/*
|--------------------------------------------------------------------------
| Health Check Route
|--------------------------------------------------------------------------
|
| Simple health check endpoint for load balancers and monitoring.
| Returns 200 OK if the application is running.
|
*/

Route::get('health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now()->toIso8601String(),
        'service' => 'registry-service',
    ]);
})->name('health');
