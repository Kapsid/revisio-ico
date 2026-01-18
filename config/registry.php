<?php

/**
 * Registry Service Configuration
 *
 * This configuration file contains settings specific to
 * the company registry microservice.
 *
 * Pattern: Centralized Configuration
 * All registry-related settings are in one place, making
 * it easy to modify behavior without touching code.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Time-To-Live (TTL)
    |--------------------------------------------------------------------------
    |
    | How long (in hours) to cache company data from registries.
    | Data older than this will be refreshed on next request.
    | Default: 24 hours as per requirements.
    |
    */

    'cache_ttl_hours' => (int) env('REGISTRY_CACHE_TTL', 24),

    /*
    |--------------------------------------------------------------------------
    | Supported Countries
    |--------------------------------------------------------------------------
    |
    | List of supported country codes and their registry providers.
    | Each country maps to a specific service class that handles
    | the actual API communication.
    |
    */

    'supported_countries' => ['cz', 'sk', 'pl'],

    /*
    |--------------------------------------------------------------------------
    | Czech Republic - ARES Registry
    |--------------------------------------------------------------------------
    |
    | Configuration for Czech company registry (ARES).
    | Uses the h4kuna/ares package.
    |
    */

    'cz' => [
        'provider' => \App\Services\Registry\Providers\CzechRegistryProvider::class,
        'timeout' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Slovakia - ORSR Registry
    |--------------------------------------------------------------------------
    |
    | Configuration for Slovak company registry (ORSR).
    | Uses the lubosdz/parser-orsr package.
    |
    */

    'sk' => [
        'provider' => \App\Services\Registry\Providers\SlovakRegistryProvider::class,
        'timeout' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Poland - GUS Registry
    |--------------------------------------------------------------------------
    |
    | Configuration for Polish company registry (GUS/REGON).
    | Uses the gusapi/gusapi package.
    | Requires API key from: https://api.stat.gov.pl/Home/RegonApi
    |
    */

    'pl' => [
        'provider' => \App\Services\Registry\Providers\PolishRegistryProvider::class,
        'api_key' => env('GUS_API_KEY', ''),
        'environment' => env('GUS_API_ENV', 'dev'), // 'dev' or 'prod'
        'timeout' => 30,
    ],

];
