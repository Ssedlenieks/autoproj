<?php

return [
    'paths' => ['api/*', 'login', 'logout', 'register', 'me', 'profile', 'change-password', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://autoproj.rvtdev.tech',
        'http://localhost:5173',
        'http://localhost:8000',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];