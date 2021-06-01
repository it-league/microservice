<?php

namespace ITLeague\Microservice\Http\Services;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

abstract class BaseService
{
    public function __construct(private array $config)
    {
    }

    protected function client(): PendingRequest
    {
        $client = Http::baseUrl("{$this->config['base_uri']}")
            ->timeout($this->config['timeout'])
            ->withHeaders(['Accept-Language' => app()->getLocale()])
            ->withoutVerifying();

        if (auth()->check()) {
            $client->withHeaders(request()->header('Authorization'));
        }

        return $client;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $data
     * @param array $files
     *
     * @return array|null
     * @throws \Exception
     */
    protected function apiCall(string $method, string $path, array $data = [], array $files = []): ?array
    {
        return $this->call($method, $path, $data, $files);
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $data
     * @param array $files
     *
     * @return array|null
     * @throws \Exception
     */
    protected function privateCall(string $method, string $path, array $data = [], array $files = []): ?array
    {
        return $this->call($method, $path, $data, $files, true);
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $data
     * @param array $files
     * @param bool $private
     *
     * @return array|null
     * @throws \Exception
     */
    protected function call(string $method, string $path, array $data = [], array $files = [], bool $private = false): ?array
    {
        try {
            $client = $this->client();

            foreach ($files as $field => $file) {
                $client = $client->attach($field, $file['content'], $file['name'] ?? Str::random(32));
                $data = self::getFlattenData($data);
            }

            /** @var \Illuminate\Http\Client\Response $response */
            $response = $client->{$method}(($private ? 'private/' : 'api/') . "{$this->config['prefix']}/$path", $data)->throw();
            return $response->json('data', $response->json()) ?? null;
        } catch (RequestException $e) {
            $error = $e->response->json('error');

            $detail = Arr::get($error, 'detail', $e->getMessage());
            $status = Arr::get($error, 'status', 500);

            if (is_array($detail)) {
                throw ValidationException::withMessages($detail);
            } else {
                throw new Exception($detail, $status);
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
