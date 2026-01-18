<?php

return [
    'cache_ttl_hours' => (int) env('REGISTRY_CACHE_TTL', 24),

    'supported_countries' => ['cz', 'sk', 'pl'],

    'cz' => [
        'provider' => \App\Services\Registry\Providers\CzechRegistryProvider::class,
        'timeout' => 30,
    ],

    'sk' => [
        'provider' => \App\Services\Registry\Providers\SlovakRegistryProvider::class,
        'timeout' => 30,
    ],

    'pl' => [
        'provider' => \App\Services\Registry\Providers\PolishRegistryProvider::class,
        'api_key' => env('GUS_API_KEY', ''),
        'environment' => env('GUS_API_ENV', 'dev'),
        'timeout' => 30,
    ],
];
