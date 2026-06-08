<?php
return [
    'driver' => env('SESSION_DRIVER', 'file'),
    'lifetime' => 120,
    'expire_on_close' => false,
    'lottery' => [2, 100],
    'encrypt' => false,
    'files' => storage_path('framework/sessions'),
    'cookie' => 'pvp_session',
    'path' => '/',
    'secure' => env('SESSION_SECURE_COOKIE'),
    'http_only' => true,
    'same_site' => 'lax',
    'domain' => env('SESSION_DOMAIN', null),
];
