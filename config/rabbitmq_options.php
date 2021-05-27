<?php

/**
 * This is an example of queue connection configuration.
 * It will be merged into config/queue.php.
 * You need to set proper values in `.env`.
 */
return [

        'queue' => [
            'exchange' => env('RABBITMQ_EXCHANGE', 'service'),
            'exchange_type' => env('RABBITMQ_EXCHANGE_TYPE', 'topic'),
            'exchange_routing_key' => "",
            'prioritize_delayed_messages' => false,
            'queue_max_priority' => 10,
//            'reroute_failed' => true,
//            'failed_exchange' => 'failed-exchange',
//            'failed_routing_key' => 'event_failed',
        ],
        'exchange' => [
            'name' => env('RABBITMQ_EXCHANGE', 'service'),
            'declare' => env('RABBITMQ_EXCHANGE_DECLARE', true),
            'type' => env('RABBITMQ_EXCHANGE_TYPE', 'topic'),
            'passive' => env('RABBITMQ_EXCHANGE_PASSIVE', false),
            'durable' => env('RABBITMQ_EXCHANGE_DURABLE', true),
            'auto_delete' => env('RABBITMQ_EXCHANGE_AUTODELETE', false),
            'arguments' => env('RABBITMQ_EXCHANGE_ARGUMENTS'),
        ]

];
