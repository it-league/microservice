<?php


namespace ITLeague\Microservice\Casts;


use Cache;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Str;
use ITLeague\Microservice\Http\Helpers\Storage;

class File implements CastsAttributes
{
    protected const ttl = 60 * 60 * 24 * 30; // month

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return array|null
     * @throws \Exception
     */
    final public function get($model, string $key, $value, array $attributes): ?array
    {
        if (Str::length((string)$value) === 36) {
            return Cache::remember(
                md5("file:$value"),
                static::ttl,
                fn() => Storage::info($value)
            );
        }
        return null;
    }

    final public function set($model, string $key, $value, array $attributes)
    {
        return $attributes[$key] ?? null;
    }
}
