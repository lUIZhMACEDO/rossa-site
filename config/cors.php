<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | API routes are open to all origins so external research tools and
    | third-party consumers can access the public endpoints. Authenticated
    | routes still require a valid Sanctum token — CORS only controls which
    | browser origins may initiate the request, not who can read the data.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'DELETE', 'OPTIONS'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
