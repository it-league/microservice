<?php

namespace itleague\microservice;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;
use itleague\microservice\Console\Commands\LanguageTableCreate;
use itleague\microservice\Http\Helpers\RequestQuery;
use itleague\microservice\Models\Language;
use itleague\microservice\Repositories\Decorators\LanguageCachingRepository;
use itleague\microservice\Repositories\Interfaces\LanguageRepositoryInterface;
use itleague\microservice\Repositories\LanguageRepository;
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
        /* Подключение репозитория для работы с языками */
        $this->app->singleton(LanguageRepositoryInterface::class, function () {
            $baseRepo = new LanguageRepository(new Language);
            return new LanguageCachingRepository($baseRepo);
        });

        /* Расширение штатного валидатора */
        $this->app['validator']->resolver(function ($translator, $data, $rules, $messages) {
            return new Validator($translator, $data, $rules, $messages);
        });

        /* Расширение хэлпера request() для возврата полей filter, sort, fields и page */
        $this->app['request']->macro('page', function (?string $field = null) {
            return RequestQuery::instance()->page($field);
        });
        $this->app['request']->macro('sort', function () {
            return RequestQuery::instance()->sort();
        });
        $this->app['request']->macro('filter', function (?string $field = null) {
            return RequestQuery::instance()->filter($field);
        });
        $this->app['request']->macro('fields', function () {
            return RequestQuery::instance()->fields();
        });

        /* Команда artisan создания миграции для таблицы языков */
        $this->commands([LanguageTableCreate::class]);

        /* Доп. методы для миграций */
        Blueprint::macro('softDeletesWithUserFields', function () {
            $this->softDeletes();
            $this->uuid('deleted_by')->nullable();
        });
        Blueprint::macro('timestampsWithUserFields', function () {
            $this->timestamp('created_at')->useCurrent();
            $this->timestamp('updated_at')->useCurrent();
            $this->uuid('created_by');
            $this->uuid('updated_by');
        });
    }
}
