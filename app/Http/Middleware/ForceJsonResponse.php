<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Force JSON Response Middleware
 *
 * This middleware ensures all API responses are JSON formatted.
 * It sets the Accept header to application/json for incoming requests.
 *
 * Pattern: Middleware (Chain of Responsibility)
 * Each middleware can process the request before/after it reaches
 * the controller, forming a processing pipeline.
 */
final class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        // Force Accept header to JSON for API routes
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
