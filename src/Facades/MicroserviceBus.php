<?php


namespace ITLeague\Microservice\Facades;


use Illuminate\Support\Facades\Facade;

/**
 * @method static void push(string $event, array $data)
 * @method static void listen(array|string $events, string $handler)
 */

class MicroserviceBus extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'microservice.bus';
    }
}
