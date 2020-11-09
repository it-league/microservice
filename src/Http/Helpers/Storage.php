<?php


namespace ITLeague\Microservice\Http\Helpers;


use Auth;
use Cache;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use ITLeague\Microservice\Facades\MicroserviceBus;

class Storage
{
    protected const ttl = 60 * 60 * 24 * 30; // month

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

    private static function getCacheKey(string $fileId): string
    {
        return md5("file:$fileId");
    }

    private static function clearCache(string $fileId)
    {
        Cache::forget(static::getCacheKey($fileId));
    }

    /**
     * @param string $fileId
     * @param array|null $permission
     * @param array|null $sizes
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public static function confirm(string $fileId, ?array $permission = [], ?array $sizes = [])
    {
        $data = self::info($fileId);
        if ($data['confirmed'] === true) {
            throw ValidationException::withMessages([$fileId => 'File is already confirmed!']);
        }

        MicroserviceBus::push('file.confirm', ['id' => $fileId, 'permission' => $permission, 'sizes' => $sizes]);
        self::clearCache($fileId);
    }

    public static function permission(string $fileId, ?array $permission = [])
    {
        self::call('put', 'permission/' . $fileId, ['permission' => $permission]);
        self::clearCache($fileId);
    }

    /**
     * @param string $fileId
     *
     * @throws \Exception
     */
    public static function delete(string $fileId)
    {
        MicroserviceBus::push('file.delete', ['id' => $fileId]);
        self::clearCache($fileId);
    }

    /**
     * @param resource $file
     * @param string|null $filename
     * @param string|null $path
     *
     * @return array|null
     * @throws \Exception
     */
    public static function upload($file, ?string $filename = null, ?string $path = 'upload')
    {
        return self::call('post', 'upload', ['path' => $path], ['file' => ['name' => $filename, 'content' => $file]]);
    }

    /**
     * @param resource|string $file
     * @param string|null $filename
     * @param string|null $path
     * @param array|null $permission
     * @param array|null $sizes
     *
     * @return array|null
     * @throws \Exception
     */
    public static function uploadForce($file, ?string $filename = null, ?string $path = 'upload', ?array $permission = [], ?array $sizes = [])
    {
        return self::call(
            'post',
            'force/upload',
            ['path' => $path, 'permission' => $permission, 'sizes' => $sizes],
            ['file' => ['name' => $filename, 'content' => $file]]
        );
    }

    /**
     * @param string|null $fileId
     *
     * @return array
     * @throws \Exception
     */
    public static function info(string $fileId)
    {
        $data = Cache::remember(
            static::getCacheKey($fileId),
            static::ttl,
            fn() => self::call('get', 'info/' . $fileId)
        );
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
            foreach ($files as $field => $file) {
                $http = $http->attach($field, $file['content'], $file['name'] ?? Str::random(32));
                $data = self::getFlattenData($data);
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

    private static function getFlattenData(array $data): array
    {
        $result = [];

        foreach ($data as $subKey => $value) {
            if (is_array($value)) {
                $result = self::getFlattenDataRecursive($subKey, $value, $result);
            } else {
                $result[] = ['name' => $subKey, 'contents' => $value];
            }
        }

        return $result;
    }

    private static function getFlattenDataRecursive(string $key, array $data, array $result = []): array
    {
        foreach ($data as $subKey => $value) {
            $subKey = $key . '[' . $subKey . ']';
            if (is_array($value)) {
                $result = self::getFlattenDataRecursive($subKey, $value, $result);
            } else {
                $result[] = ['name' => $subKey, 'contents' => $value];
            }
        }

        return $result;
    }
}
