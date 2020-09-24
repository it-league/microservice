<?php


namespace ITLeague\Microservice\Http\Helpers;


use Auth;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class Storage
{
    private static function http()
    {
        static $http;

        if (! isset($http)) {
            $http = Http::baseUrl(config('microservice.storage_uri') . '/' . config('microservice.storage_prefix') . '/')->withoutVerifying();

            if (Auth::check() === true) {
                $http = $http->withHeaders(
                    [
                        'x-authenticated-userid' => Auth::id(),
                        'x-authenticated-scope' => trim(implode(' ', (array)Auth::user()->scope)),
                    ]
                );
            }
        }

        return $http;
    }

    public static function confirm(string $fileId, ?array $permission = [], ?array $sizes = [])
    {
        self::call('put', 'confirm/' . $fileId, ['permission' => $permission, 'sizes' => $sizes]);
    }

    public static function delete(string $fileId)
    {
        // TODO: игнорировать только 404 ошибки

        try {
            self::call('delete', 'delete/' . $fileId);
        } catch (Exception $e) {
        }
    }

    /**
     * @param string|null $fileId
     *
     * @return array
     * @throws \Exception
     */
    public static function info(string $fileId)
    {
        $data = self::call('get', 'info/' . $fileId);
        return $data['data'];
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $data
     *
     * @return array|null
     * @throws \Exception
     */
    private static function call(string $method, string $url, array $data = [])
    {
        try {
            $response = self::http()->$method($url, $data)->throw();
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
