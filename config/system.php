<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Toggle Cache Settings
    |--------------------------------------------------------------------------
    */
    'toggle_cache' => [
        'debug_mode' => (bool) env('TOGGLE_CACHE_DEBUG', true),
        'enabled' => (bool) env('TOGGLE_CACHE_ENABLED', true),
        'default_ttl' => (int) env('TOGGLE_CACHE_TTL', 60),
    ],

    'database_debug_mode' => (string) env('TOGGLE_DATABASE_DEBUG', false),
];
