<?php

return [

    'api_uri' => env('API_BASE_URI', 'https://gate.heroleague.ru'),
    'prefix' => env('SERVICE_PREFIX', 'api'),

    'storage' => [
        'base_uri' => env('STORAGE_SERVICE_BASE_URI', 'http://storage-nginx'),
        'prefix' => env('STORAGE_SERVICE_PREFIX', 'storage'),
        'timeout' => env('STORAGE_SERVICE_TIMEOUT', 300),
    ]
];
