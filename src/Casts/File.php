<?php


namespace ITLeague\Microservice\Casts;


use Auth;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Cache;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
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

    protected PendingRequest $http;

    protected Cache\Repository $cache;
    protected int $ttl = 60 * 60 * 24 * 30; // month

    public function __construct()
    {
        $this->cache = app('cache.store');
        $this->http = Http::baseUrl(config('microservice.storage_uri') . '/' . config('microservice.storage_prefix') . '/')->withoutVerifying();

        if (Auth::check() === true) {
            $this->http = $this->http->withHeaders([
                'x-authenticated-userid' => Auth::id(),
                'x-authenticated-scope' => trim(implode(' ', (array)Auth::user()->scope)),
            ]);
        }
    }

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
        $hash = md5("file:$value");
        return $this->cache->remember($hash, $this->ttl, function () use ($value) {
            return $this->getInfo($value);
        });
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

        // confirm new file
        if ($value && $this->force === false) {
            $this->call('put', 'confirm/' . $value, ['permission' => $this->permission, 'sizes' => $this->sizes]);
        }

        // delete old file
        if (Arr::has($attributes, $key) && Str::length($attributes[$key]) === 36) {
            $this->call('delete', 'delete/' . $attributes[$key]);

        }


        return $value;
    }

    /**
     * @param string $value
     *
     * @return array|null
     * @throws \Exception
     */
    protected function getInfo(string $value)
    {
        if ($value) {
            $data = $this->call('get', 'info/' . $value);
            return $data['data'];
        }

        return null;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $data
     *
     * @return array|null
     * @throws \Exception
     */
    private function call(string $method, string $url, array $data = [])
    {
        try {

            $response = $this->http->$method($url, $data)->throw();
            if (($body = json_decode($response->body(), true)) && Arr::has((array)$body, 'data')) {
                return json_decode($response->body(), true);
            }

            return null;

        } catch (RequestException $e) {

            if (($content = json_decode($e->response->body(), true)) && Arr::has((array)$content, 'error')) {
                throw new Exception('Storage service error: ' . $content['error']['detail'], $content['error']['status']);
            } else {
                throw new Exception('Storage service error: ' . $e->getMessage(), 500);
            }
        }
    }
}
