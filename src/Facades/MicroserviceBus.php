<?php


namespace ITLeague\Microservice\Facades;


use Illuminate\Support\Facades\Facade;

class MicroserviceBus extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'microservice.bus';
    }
}
