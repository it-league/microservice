<?php

namespace ITLeague\Microservice\Http\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Str;

abstract class BaseService
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    protected function client(): PendingRequest {
        $client = Http::baseUrl("{$this->config['base_uri']}/{$this->config['prefix']}")
            ->timeout($this->config['timeout'])
            ->withHeaders(['Accept-Language' => app()->getLocale()])
            ->withoutVerifying();

        if (Auth::check()) {
            $client->withHeaders(
                [
                    'x-authenticated-userid' => Auth::id(),
                    'x-authenticated-scope' => trim(implode(' ',  (array)(Auth::user()->scope ?? [])))
                ]
            );
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
    protected function query(string $method, string $path, array $data = [], array $files = []): ?array
    {
        try {

            $client = $this->client();

            foreach ($files as $field => $file) {
                $client = $client->attach($field, $file['content'], $file['name'] ?? Str::random(32));
                $data = self::getFlattenData($data);
            }

            /** @var \Illuminate\Http\Client\Response $response */
            $response = $client->{$method}($path, $data)->throw();
            return $response->json('data', $response->json()) ?? null;

        } catch (RequestException $e) {
            $error = $e->response->json('error');

            throw new Exception(
                Arr::get($error, 'detail', $e->getMessage()),
                Arr::get($error, 'status', 500)
            );
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
