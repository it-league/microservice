<?php


namespace ITLeague\Microservice\Casts;


use Auth;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

abstract class File implements CastsAttributes
{
    protected array $permission = [
        'hideAll' => true
    ];

    protected array $sizes = [];

    protected bool $force = false;


    public function get($model, string $key, $value, array $attributes)
    {
        return $value;
    }


    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return array|mixed|string
     * @throws \GuzzleHttp\Exception\GuzzleException|\Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function set($model, string $key, $value, array $attributes)
    {

        if (Auth::check() !== true) {
            throw new AuthorizationException('Can`t save file without authorization');
        }

        $http = Http::withHeaders([
            'x-authenticated-userid' => Auth::id(),
            'x-authenticated-scopes' => implode(' ', (array)Auth::user()->scope),
        ])->baseUrl(config('microservice.storage_uri'));

        try {

            // confirm new file
            if ($value && $this->force === false) {
                $http->put('confirm/' . $value, ['permission' => $this->permission, 'sizes' => $this->sizes])->throw();
            }

            // delete old file
            if (Arr::has($attributes, $key) && Str::length($attributes[$key]) === 36) {
                $http->delete('delete/' . $attributes[$key])->throw();
            }

        } catch (RequestException $e) {

            if ($content = json_decode($e->response->body(), true)) {
                throw new Exception($content['error']['detail'], $content['error']['status']);
            } else {
                throw new Exception();
            }
        }

        return $value;
    }
}
