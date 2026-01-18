<?php

/**
 * Authentication Configuration
 *
 * This service does not use authentication.
 * AWS ECS handles IP restrictions at the infrastructure level.
 */

return [

    'defaults' => [
        'guard' => 'web',
        'passwords' => null,
    ],

    'guards' => [],

    'providers' => [],

    'passwords' => [],

];
