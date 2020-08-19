<?php

namespace itleague\microservice;

use itleague\microservice\Console\Commands\LanguageTableCreate;
use itleague\microservice\Http\Middleware\LocalizationMiddleware;
use itleague\microservice\Models\Language;
use itleague\microservice\Repositories\Decorators\LanguageCachingRepository;
use itleague\microservice\Repositories\Interfaces\LanguageRepositoryInterface;
use itleague\microservice\Repositories\LanguageRepository;
use Illuminate\Support\ServiceProvider;
use itleague\microservice\Http\Helpers\RequestQuery;
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
        $this->app->singleton(LanguageRepositoryInterface::class, function () {
            $baseRepo = new LanguageRepository(new Language);
            return new LanguageCachingRepository($baseRepo);
        });

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

        $this->commands([LanguageTableCreate::class]);

        app()->middleware([
            LocalizationMiddleware::class
        ]);
    }
}
