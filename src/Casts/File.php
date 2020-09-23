<?php


namespace ITLeague\Microservice\Casts;


use Auth;
use Cache;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ITLeague\Microservice\Http\Helpers\Storage;

abstract class File implements CastsAttributes
{
    protected array $permission = [
        'hideAll' => true
    ];

    protected array $sizes = [];
    protected bool $force = false;

    protected int $ttl = 60 * 60 * 24 * 30; // month

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return array|null
     * @throws \Exception
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return Cache::remember(
            md5("file:$value"),
            $this->ttl,
            function () use ($value) {
                return Storage::info($value);
            }
        );
    }


    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return array|mixed|string
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (Auth::check() !== true) {
            throw new AuthorizationException('Can`t save file without authorization');
        }

        if (! Arr::has($attributes, $key) || $attributes[$key] !== $value) {
            // confirm new file
            if ($value && $this->force === false) {
                Storage::confirm($value, $this->permission, $this->sizes);
            }

            // delete old file
            if (Arr::has($attributes, $key) && Str::length($attributes[$key]) === 36) {
                Storage::delete($attributes[$key]);
            }
        }

        return $value;
    }

}
