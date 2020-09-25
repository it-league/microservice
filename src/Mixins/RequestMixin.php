<?php


namespace ITLeague\Microservice\Mixins;


use Illuminate\Support\Arr;

/** @mixin \Illuminate\Http\Request */
class RequestMixin
{
    private array $cache = [];

    public function sort()
    {
        return function (): ?array {
            if (! isset($this->cache['sort'])) {
                $cache = $this->cache;
                $string = (string)request()->query('sort');

                if (strlen($string) > 0) {
                    $cache['sort'] = array_map('trim', explode(',', $string));
                } else {
                    $cache['sort'] = null;
                }
                $this->cache = $cache;
            }

            return $this->cache['sort'];
        };
    }

    public function fields()
    {
        return function (): ?array {
            if (! isset($this->cache['fields'])) {
                $cache = $this->cache;
                $string = (string)request()->query('fields');

                if (strlen($string) > 0) {
                    $cache['fields'] = array_map('trim', explode(',', $string));
                } else {
                    $cache['fields'] = null;
                }
                $this->cache = $cache;
            }

            return $this->cache['fields'];
        };
    }

    public function page()
    {
        return function (?string $field = null) {
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

    public function filter()
    {
        return function (?string $field = null) {
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
