<?php

return [
    'base_url' => env('QUOTES_API_URL', 'https://dummyjson.com'),

    'rate_limit' => [
        'max_requests' => (int) env('QUOTES_RATE_LIMIT_MAX', 30),
        'window_seconds' => (int) env('QUOTES_RATE_LIMIT_WINDOW', 60),
        'cache_key' => env('QUOTES_RATE_LIMIT_KEY', 'quotes_bridge:rate_limit'),
    ],

    'cache' => [
        'store' => env('QUOTES_CACHE_STORE'),
        'key' => env('QUOTES_CACHE_KEY', 'quotes_bridge:store'),
        'ttl' => (int) env('QUOTES_CACHE_TTL', 3600),
    ],

    'pagination' => [
        'per_page' => (int) env('QUOTES_PER_PAGE', 20),
    ],
];
