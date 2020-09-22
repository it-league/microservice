<?php

namespace ITLeague\Microservice;

use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Flipbox\LumenGenerator\LumenGeneratorServiceProvider;
use Gate;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Redis\RedisServiceProvider;
use Illuminate\Support\ServiceProvider;
use ITLeague\Microservice\Console\Commands\LanguageTableCreate;
use ITLeague\Microservice\Exceptions\Handler;
use ITLeague\Microservice\Mixins\BlueprintMixin;
use ITLeague\Microservice\Mixins\BuilderMixin;
use ITLeague\Microservice\Mixins\RequestMixin;
use ITLeague\Microservice\Models\Language;
use ITLeague\Microservice\Models\User;
use ITLeague\Microservice\Repositories\Decorators\LanguageCachingRepository;
use ITLeague\Microservice\Repositories\Interfaces\LanguageRepositoryInterface;
use ITLeague\Microservice\Repositories\LanguageRepository;
use ITLeague\Microservice\Validators\Validator;
use LumenMiddlewareTrimOrConvertString\ConvertEmptyStringsToNull;
use LumenMiddlewareTrimOrConvertString\TrimStrings;

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
        $configPath = __DIR__ . '/../config/microservice.php';
        $this->mergeConfigFrom($configPath, 'microservice');

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

        /* Обработчик ошибок */
        $this->app->singleton(
            ExceptionHandler::class,
            Handler::class
        );

        /* Расширения для фасадов */
        Request::mixin(new RequestMixin());
        Blueprint::mixin(new BlueprintMixin());
        Builder::mixin(new BuilderMixin());

        /* Права доступа по-умолчанию */
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
            return null;
        });
        Gate::define('admin', function (User $user) {
            return $user->isAdmin();
        });
    }

    public function boot()
    {
        $this->app['auth']->viaRequest('api', function ($request) {
            if (! $request->hasHeader('x-authenticated-userid')) {
                return null;
            }

            return new User([
                'id' => $request->header('x-authenticated-userid'),
                'scope' => explode(' ', $request->header('x-authenticated-scope')),
            ]);
        });

        app()->middleware([
            TrimStrings::class,
            ConvertEmptyStringsToNull::class
        ]);
    }
}
