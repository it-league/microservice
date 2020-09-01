<?php


namespace ITLeague\Microservice\Casts;


use Exception;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
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

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return array|mixed|string
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function set($model, string $key, $value, array $attributes)
    {
        // confirm new file
        if ($value && $this->force === false) {
            $response = Http::put(config('microservice.storage_uri') . '/confirm/' . $value, ['permissions' => $this->permissions, 'sizes' => $this->sizes]);
            static::checkResponse($response, 204);
        }

        // delete old file
        if ($attributes[$key]) {
            $response = Http::delete(config('microservice.storage_uri') . '/delete/' . $attributes[$key]);
            static::checkResponse($response, 204);
        }

        return $value;
    }

    /**
     * @param \Illuminate\Http\Client\Response $response
     * @param int $success
     *
     * @throws \Illuminate\Http\Client\RequestException
     * @throws \Exception
     */
    private static function checkResponse(Response $response, int $success = 200) {
        if ($response->status() !== $success) {
            if ($content = json_decode($response->body(), true)) {
                throw new Exception($content['error']['detail'], $content['error']['code']);
            } else {
                throw new RequestException($response);
            }
        }
    }
}
