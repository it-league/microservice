<?php

namespace ITLeague\Microservice\Traits\Models;

use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait Loggable
{
    public static function bootLoggable()
    {
        if (app()->environment() !== 'testing') {
            static::registerLogger();
        }
    }

    #[ArrayShape([
        'model' => "string",
        'attributes' => "string",
        'user' => "string",
        'ip' => "null|string",
        'lang' => "string"
    ])]
    private static function info(
        self $model
    ): array {
        return [
            'model' => get_class($model),
            'attributes' => json_encode($model->getAttributes()),
            'user' => auth()->id(),
            'ip' => request()->ip(),
            'lang' => app()->getLocale(),
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
