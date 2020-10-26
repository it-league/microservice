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

    public static function permission(string $fileId, ?array $permission = [])
    {
        self::call('put', 'permission/' . $fileId, ['permission' => $permission]);
    }

    /**
     * @param string $fileId
     *
     * @throws \Exception
     */
    public static function delete(string $fileId)
    {
        try {
            self::call('delete', 'delete/' . $fileId);
        } catch (Exception $e) {
            if ($e->getCode() !== 404) {
                throw $e;
            }
        }
    }

    /**
     * @param resource $file
     * @param string|null $path
     *
     * @throws \Exception
     */
    public static function upload($file, ?string $path = 'upload')
    {
        self::call('post', 'upload', ['path' => $path], ['file' => $file]);
    }

    /**
     * @param resource $file
     * @param string|null $path
     * @param array|null $permission
     * @param array|null $sizes
     *
     * @throws \Exception
     */
    public static function uploadForce($file, ?string $path = 'upload', ?array $permission = [], ?array $sizes = [])
    {
        self::call('post', 'force/upload', ['path' => $path, 'permission' => $permission, 'sizes' => $sizes], ['file' => $file]);
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
     * @param array $files
     *
     * @return array|null
     * @throws \Exception
     */
    private static function call(string $method, string $url, array $data = [], array $files = [])
    {
        try {
            $http = self::http();
            foreach ($files as $name => $file) {
                $http = $http->attach($name, $file);
            }

            $response = $http->$method($url, $data)->throw();
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
