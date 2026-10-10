<?php

// Dozvoljava Angular aplikaciji (localhost:4200) da poziva API (localhost:8000).
return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(
        explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:4200,http://127.0.0.1:4200'))
    ),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Koristimo Bearer token (ne kolacice), pa credentials nisu potrebni
    'supports_credentials' => false,

];
