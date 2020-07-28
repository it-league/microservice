<?php

namespace itleague\microservice;

use Illuminate\Support\ServiceProvider;
use itleague\microservice\Helpers\QueryParameters;
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

        $this->app->singleton('query', function () {
            return new QueryParameters();
        });
    }
}
