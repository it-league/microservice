<?php

return [

    'storage_uri' => env('STORAGE_SERVICE_BASE_URI', 'https://storage-nginx'),
    'storage_prefix' => env('STORAGE_SERVICE_PREFIX', 'storage'),
    'api_uri' => env('API_BASE_URI', 'https://gate.heroleague.ru'),
    'prefix' => env('SERVICE_PREFIX', 'api')
];
