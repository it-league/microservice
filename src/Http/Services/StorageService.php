<?php


namespace ITLeague\Microservice\Http\Services;


use Cache;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use ITLeague\Microservice\Facades\MicroserviceBus;

class StorageService extends BaseService
{
    private const ttl = 60 * 60 * 24 * 30; // month

    private function getCacheKey(string $fileId): string
    {
        return md5("file:$fileId");
    }

    private function clearCache(string $fileId): bool
    {
        return Cache::forget($this->getCacheKey($fileId));
    }

    /**
     * @param string $fileId
     * @param array|null $permission
     * @param array|null $sizes
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function confirm(string $fileId, ?array $permission = [], ?array $sizes = []): void
    {
        $data = $this->info($fileId);
        if (Arr::get($data, 'confirmed') === true) {
            throw ValidationException::withMessages([$fileId => __('File is already confirmed')]);
        }

        MicroserviceBus::push('file.confirm', ['id' => $fileId, 'permission' => $permission, 'sizes' => $sizes]);
        $this->clearCache($fileId);
    }

    /**
     * @param string $fileId
     * @param array|null $permission
     *
     * @throws \Exception
     */
    public function permission(string $fileId, ?array $permission = []): void
    {
        $this->apiCall('put', "permission/$fileId", ['permission' => $permission]);
        self::clearCache($fileId);
    }

    /**
     * @param string $fileId
     *
     * @throws \Exception
     */
    public function delete(string $fileId): void
    {
        MicroserviceBus::push('file.delete', ['id' => $fileId]);
        $this->clearCache($fileId);
    }

    /**
     * @param resource|string $file
     * @param string|null $filename
     * @param string|null $path
     *
     * @return array
     * @throws \Exception
     */
    public function upload($file, ?string $filename = null, ?string $path = 'upload'): array
    {
        return $this->apiCall('post', 'upload', ['path' => $path], ['file' => ['name' => $filename, 'content' => $file]]);
    }

    /**
     * @param resource|string $file
     * @param string|null $filename
     * @param string|null $path
     * @param array|null $permission
     * @param array|null $sizes
     *
     * @return array
     * @throws \Exception
     */
    public function uploadForce($file, ?string $filename = null, ?string $path = 'upload', ?array $permission = [], ?array $sizes = []): array
    {
        return $this->privateCall(
            'post',
            'force/upload',
            ['path' => $path, 'permission' => $permission, 'sizes' => $sizes],
            ['file' => ['name' => $filename, 'content' => $file]]
        );
    }

    /**
     * @param string $fileId
     *
     * @return array
     * @throws \Exception
     */
    public function info(string $fileId): array
    {
        return Cache::remember(
            $this->getCacheKey($fileId),
            self::ttl,
            fn() => (array)$this->apiCall('get', "info/$fileId")
        );
    }
}
