<?php

return [
    'driver' => 'custom',
    'via' => \ITLeague\Microservice\Logger\LogstashLogger::class,
    'name' => env('LOGSTASH_CHANNEL_NAME', 'logstash'),
    'host' => env('LOGSTASH_HOST', '127.0.0.1'),
    'port' => env('LOGSTASH_PORT', 5000),
];
