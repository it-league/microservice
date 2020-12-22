<?php

namespace ITLeague\Microservice\Traits;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasLogger
{
    public static function bootHasLogger()
    {
        static::registerLogger();
    }

    private static function info(self $model): array
    {
        return [
            'model' => get_class($model),
            'id' => (string)$model->getKey(),
            'user' => Auth::id(),
            'ip' => Request::ip(),
            'lang' => App::getLocale(),
        ];
    }

    protected static function registerLogger(): void
    {
        static::created(
            fn(self $model) => Log::info('Model created', static::info($model))
        );

        static::updated(
            fn(self $model) => Log::info('Model updated', static::info($model))
        );

        static::deleted(
            fn(self $model) => Log::info('Model deleted', static::info($model))
        );
    }
}
