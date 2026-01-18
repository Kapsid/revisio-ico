<?php

/**
 * CORS Configuration
 *
 * Configure Cross-Origin Resource Sharing (CORS) settings.
 * Since this is a backend microservice accessed via AWS internal network,
 * CORS is permissive by default.
 */

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
