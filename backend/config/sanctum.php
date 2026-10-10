<?php

// Sanctum: Angular salje token u zaglavlju "Authorization: Bearer <token>".
return [

    'stateful' => [],

    'guard' => ['web'],

    // null = token ne istice (za skolski projekat); u produkciji postavi broj minuta
    'expiration' => null,

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],

];
