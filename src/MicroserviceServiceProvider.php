<?php

namespace itleague\microservice;

use Illuminate\Support\ServiceProvider;
use itleague\microservice\Helpers\Http\RequestQuery;
use itleague\microservice\Validators\Validator;

class MicroserviceServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app['validator']->resolver(function ($translator, $data, $rules, $messages) {
            return new Validator($translator, $data, $rules, $messages);
        });

        $this->app['request']->macro('page', function (?string $field = null) {
            return RequestQuery::instance()->page($field);
        });
        $this->app['request']->macro('filter', function (?string $field = null) {
            return RequestQuery::instance()->filter($field);
        });
        $this->app['request']->macro('fields', function () {
            return RequestQuery::instance()->fields();
        });
    }
}
