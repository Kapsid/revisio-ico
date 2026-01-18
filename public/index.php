<?php

/**
 * Registry Service - Entry Point
 *
 * This is the single entry point for all HTTP requests.
 * Laravel uses the Front Controller pattern - all requests
 * flow through this file, which bootstraps the application
 * and delegates to the appropriate route handler.
 */

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
