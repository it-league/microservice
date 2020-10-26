<?php


namespace ITLeague\Microservice\Http\Bus;


interface EventHandler
{
    public function handle(array $data): void;
}
