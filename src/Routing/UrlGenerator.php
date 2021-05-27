<?php


namespace ITLeague\Microservice\Routing;


use Illuminate\Support\Arr;

class UrlGenerator extends \Laravel\Lumen\Routing\UrlGenerator
{
    /**
     * {@inheritDoc}
     */
    protected function replaceRouteParameters($route, &$parameters = [])
    {
        return preg_replace_callback(
            '/\{([a-zA-Z_]*?)(:[^\/]*)?(\{[0-9,]+\})?\}/',
            function ($m) use (&$parameters) {
                return isset($parameters[$m[1]]) ? Arr::pull($parameters, $m[1]) : $m[0];
            },
            $route
        );
    }
}
