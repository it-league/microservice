<?php


namespace ITLeague\Microservice\Http\Bus;


interface EventHandler
{
    public function handle(string $event, array $data): void;
}
