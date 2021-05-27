<?php


namespace ITLeague\Microservice\Facades;


use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void push(string $event, array|JsonResource $data)
 * @method static void listen(array|string $events, string $handler)
 */

class MicroserviceBus extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'microservice.bus';
    }
}
