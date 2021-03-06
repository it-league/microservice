<?php


namespace ITLeague\Microservice\Mixins;


use Closure;
use Illuminate\Support\Arr;

/** @mixin \Illuminate\Http\Request */
class RequestMixin
{
    private array $cache = [];

    public function sort(): Closure
    {
        return function (): ?array {
            if (! isset($this->cache['sort'])) {
                $cache = $this->cache;

                if (request()->has('sort')) {
                    $cache['sort'] = array_map('trim', explode(',', (string)request()->query('sort')));
                } else {
                    $cache['sort'] = null;
                }
                $this->cache = $cache;
            }

            return $this->cache['sort'];
        };
    }

    public function fields(): Closure
    {
        return function (): ?array {
            if (! isset($this->cache['fields'])) {
                $cache = $this->cache;

                if (request()->has('fields')) {
                    $cache['fields'] = array_map('trim', explode(',', (string)request()->query('fields')));
                } else {
                    $cache['fields'] = null;
                }
                $this->cache = $cache;
            }

            return $this->cache['fields'];
        };
    }

    public function page(): Closure
    {
        return function (?string $field = null): array|int|bool {
            if (! isset($this->cache['page'])) {
                $cache = $this->cache;

                $cache['page'] = array_map('intval', (array)request()->query('page'));
                $cache['page'] = [
                    'size' => Arr::get($cache['page'], 'size', 10),
                    'number' => Arr::get($cache['page'], 'number', 1),
                    'all' => Arr::get($cache['page'], 'all', 0) === 1
                ];
                $cache['page']['size'] = $cache['page']['size'] <= 0 ? 10 : $cache['page']['size'];
                $cache['page']['number'] = $cache['page']['number'] < 1 ? 1 : $cache['page']['number'];


                $this->cache = $cache;
            }

            if (is_null($field)) {
                return $this->cache['page'];
            } else {
                return $this->cache['page'][$field];
            }
        };
    }

    public function filter(): Closure
    {
        return function (?string $field = null): array|string {
            if (! isset($this->cache['filter'])) {
                $cache = $this->cache;
                $cache['filter'] = (array)request()->query('filter');
                $this->cache = $cache;
            }

            if (is_null($field)) {
                return $this->cache['filter'];
            } else {
                return $this->cache['filter'][$field];
            }
        };
    }
}
