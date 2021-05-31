<?php

namespace ITLeague\Microservice;

use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Flipbox\LumenGenerator\LumenGeneratorServiceProvider;
use Gate;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Illuminate\Http\Request;
use Illuminate\Redis\RedisServiceProvider;
use Illuminate\Support\Fluent;
use Illuminate\Support\ServiceProvider;
use ITLeague\Microservice\Console\Commands\LanguageTableCreate;
use ITLeague\Microservice\Exceptions\Handler;
use ITLeague\Microservice\Http\Bus\Adapter;
use ITLeague\Microservice\Http\Services\StorageService;
use ITLeague\Microservice\Mixins\BlueprintMixin;
use ITLeague\Microservice\Mixins\BuilderMixin;
use ITLeague\Microservice\Mixins\RequestMixin;
use ITLeague\Microservice\Models\Language;
use ITLeague\Microservice\Models\User;
use ITLeague\Microservice\Repositories\Decorators\LanguageCachingRepository;
use ITLeague\Microservice\Repositories\Interfaces\LanguageRepositoryInterface;
use ITLeague\Microservice\Repositories\LanguageRepository;
use ITLeague\Microservice\Routing\UrlGenerator;
use ITLeague\Microservice\Validators\Validator;
use Laravel\Lumen\Application;
use Laravel\Lumen\Http;
use LumenMiddlewareTrimOrConvertString\ConvertEmptyStringsToNull;
use LumenMiddlewareTrimOrConvertString\TrimStrings;
use VladimirYuldashev\LaravelQueueRabbitMQ\Console\ConsumeCommand;
use VladimirYuldashev\LaravelQueueRabbitMQ\LaravelQueueRabbitMQServiceProvider;

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
        $this->mergeConfigFrom(__DIR__ . '/../config/microservice.php', 'microservice');

        app()->register(RedisServiceProvider::class);
        app()->register(LaravelQueueRabbitMQServiceProvider::class);
        if (config('app.debug') === true) {
            app()->register(IdeHelperServiceProvider::class);
            app()->register(LumenGeneratorServiceProvider::class);
        }

        $this->registerTranslations();
        $this->registerBus();
        $this->registerDefaultGates();
        $this->registerStorage();
        $this->registerLogging();


        /* Расширение штатного валидатора */
        $this->app['validator']->resolver(
            fn($translator, $data, $rules, $messages) => new Validator($translator, $data, $rules, $messages)
        );

        /* Небольшое изменение в регулярке для корректного разбора роутов с проверкой uuid */
        $this->app->singleton('url', fn() => new UrlGenerator(Application::getInstance()));

        /* Обработчик ошибок */
        $this->app->singleton(
            ExceptionHandler::class,
            Handler::class
        );

        /* Расширения для фасадов */
        Request::mixin(new RequestMixin());
        Blueprint::mixin(new BlueprintMixin());
        Builder::mixin(new BuilderMixin());
    }

    private function registerTranslations(): void
    {
        $this->app->singleton(
            LanguageRepositoryInterface::class,
            fn() => new LanguageCachingRepository(new LanguageRepository(new Language()))
        );

        $this->commands([LanguageTableCreate::class]);


        $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');
    }

    private function registerBus(): void
    {
        app()->configure('queue');
        $this->mergeConfigFrom(__DIR__ . '/../config/rabbitmq_options.php', 'queue.connections.rabbitmq.options');
        $this->app->singleton('microservice.bus', fn($app) => new Adapter());

        if ($this->app->runningInConsole()) {
            $this->app->singleton(
                'microservice.bus.consumer',
                function () {
                    $isDownForMaintenance = function () {
                        return $this->app->isDownForMaintenance();
                    };

                    return new BusConsumer(
                        $this->app['queue'],
                        $this->app['events'],
                        $this->app[ExceptionHandler::class],
                        $isDownForMaintenance
                    );
                }
            );

            $this->app->singleton(
                ConsumeCommand::class,
                static function ($app) {
                    return new ConsumeCommand(
                        $app['microservice.bus.consumer'],
                        $app['cache.store']
                    );
                }
            );

            $this->commands([ConsumeCommand::class]);
        }
    }

    public function boot()
    {
        $this->app['auth']->viaRequest(
            'api',
            fn(Http\Request $request) => $request->hasHeader('x-authenticated-userid') ? new User(
                [
                    'id' => $request->header('x-authenticated-userid'),
                    'scope' => explode(' ', $request->header('x-authenticated-scope')),
                ]
            ) : null
        );

        app()->middleware(
            [
                TrimStrings::class,
                ConvertEmptyStringsToNull::class
            ]
        );
    }

    private function registerDefaultGates(): void
    {
        Gate::before(
            function (User $user, string $ability) {
                if ($user->isSuperAdmin()) {
                    return true;
                }
                return null;
            }
        );
        Gate::define(
            'admin',
            fn(User $user) => $user->isAdmin()
        );
    }

    private function registerStorage(): void
    {
        $this->app->singleton('storage.service', fn() => new StorageService(config('microservice.storage')));
    }

    private function registerLogging()
    {
        app()->configure('logging');
        $this->mergeConfigFrom(__DIR__ . '/../config/logstash.php', 'logging.channels.logstash');
    }
}
