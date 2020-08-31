<?php


namespace ITLeague\Microservice\Casts;


use Exception;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Http;

abstract class File implements CastsAttributes
{
    protected array $permissions = [
        'hideAll' => true
    ];

    protected array $sizes = [];

    protected bool $force = false;


    public function get($model, string $key, $value, array $attributes)
    {
        return $value;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        try {

            if ($attributes[$key]) {
                Http::delete(config('microservice.storage_uri') . '/delete/' . $attributes[$key]);
            }

            if ($this->force === false) {
                Http::put(config('microservice.storage_uri') . '/confirm/' . $attributes[$key], ['permissions' => $this->permissions, 'sizes' => $this->sizes]);
            }

        } catch (Exception $e) {

        }

        return $value;
    }
}
