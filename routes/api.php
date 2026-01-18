<?php

use App\Http\Controllers\Api\CompanyController;
use Illuminate\Support\Facades\Route;

Route::prefix('company')->group(function () {
    Route::get('info/{countryCode}/{companyId}', [CompanyController::class, 'show'])
        ->name('company.info')
        ->where('countryCode', 'cz|sk|pl')
        ->where('companyId', '[0-9]+');
});

Route::get('health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now()->toIso8601String(),
        'service' => 'registry-service',
    ]);
})->name('health');
