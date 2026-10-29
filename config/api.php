<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API terms and licensing URL
    |--------------------------------------------------------------------------
    |
    | Link shown on the API page, Scramble docs, and token request form.
    | When null, the site terms-of-use page is used (about.terms-of-use).
    |
    */
    'terms_url' => env('API_TERMS_URL'),

    /*
    |--------------------------------------------------------------------------
    | API availability
    |--------------------------------------------------------------------------
    |
    | When disabled, /api/* routes return 503 JSON and API pages show a notice.
    | Set API_ENABLED=true when the API is ready for use.
    |
    */
    'enabled' => env('API_ENABLED', false),

    'disabled_message' => env(
        'API_DISABLED_MESSAGE',
        'The ROSSA API is temporarily unavailable while we finish development. Please try again later.'
    ),
];
