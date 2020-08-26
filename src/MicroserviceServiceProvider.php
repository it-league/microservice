<?php

namespace itleague\microservice;

use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Flipbox\LumenGenerator\LumenGeneratorServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Redis\RedisServiceProvider;
use Illuminate\Support\ServiceProvider;
use itleague\microservice\Console\Commands\LanguageTableCreate;
use itleague\microservice\Mixins\BlueprintMixin;
use itleague\microservice\Mixins\BuilderMixin;
use itleague\microservice\Mixins\RequestMixin;
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
     * @throws \ReflectionException
     */
    public function register(): void
    {
        app()->register(RedisServiceProvider::class);
        if (config('app.debug') === true) {
            app()->register(IdeHelperServiceProvider::class);
            app()->register(LumenGeneratorServiceProvider::class);
        }

        /* Подключение репозитория для работы с языками */
        $this->app->singleton(LanguageRepositoryInterface::class, function () {
            $baseRepo = new LanguageRepository(new Language);
            return new LanguageCachingRepository($baseRepo);
        });

        /* Расширение штатного валидатора */
        $this->app['validator']->resolver(function ($translator, $data, $rules, $messages) {
            return new Validator($translator, $data, $rules, $messages);
        });

        /* Команда artisan создания миграции для таблицы языков */
        $this->commands([LanguageTableCreate::class]);

        Request::mixin(new RequestMixin());
        Blueprint::mixin(new BlueprintMixin());
        Builder::mixin(new BuilderMixin());
    }
}
