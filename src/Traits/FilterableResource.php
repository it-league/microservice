<?php


namespace ITLeague\Microservice\Traits;


use Illuminate\Support\Arr;

trait FilterableResource
{
    private function fields(array $data)
    {
        return Arr::only($data, request()->fields() ?? array_keys($data));
    }
}
