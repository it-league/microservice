<?php

namespace ITLeague\Microservice\Traits;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasLogger
{
    protected static function registerLogger()
    {
        static::created(
            fn($model) => Log::info(
                'Model created',
                [
                    'model' => $model->getAttributes(),
                    'user' => Auth::id(),
                    'ip' => Request::ip(),
                    'lang' => App::getLocale(),
                ]
            )
        );

        static::updated(
            fn($model) => Log::info(
                'Model updated',
                [
                    'model' => $model->getAttributes(),
                    'user' => Auth::id(),
                    'ip' => Request::ip(),
                    'lang' => App::getLocale(),
                ]
            )
        );

        static::deleted(
            fn($model) => Log::info(
                'Model deleted',
                [
                    'model' => $model->getAttributes(),
                    'user' => Auth::id(),
                    'ip' => Request::ip(),
                    'lang' => App::getLocale(),
                ]
            )
        );
    }
}
