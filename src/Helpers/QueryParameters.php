<?php


namespace itleague\microservice\Helpers;


use Illuminate\Support\Arr;

class QueryParameters
{
    private $fields = false;
    private array $page;
    private $query;
    private $filter;

    public function fields(): ?array
    {
        if ($this->fields === false) {
            $fields = (string)app('request')->query('fields');
            if (strlen($fields) > 0) {
                $this->fields = array_map('trim', explode(',', $this->fields));
            } else {
                $this->fields = null;
            }
        }

        return $this->fields;
    }

    /**
     * @param string|null $field
     *
     * @return array|mixed|null
     */
    public function page(string $field = null)
    {
        if (! isset($this->page)) {
            $this->page = array_map('intval', (array)app('request')->query('page'));
            $this->page = [
                'size' => Arr::get($this->page, 'size', 10),
                'number' => Arr::get($this->page, 'number', 1),
                'all' => Arr::get($this->page, 'all', 0) === 1
            ];
            $this->page['size'] = $this->page['size'] <= 0 ? 10 : $this->page['size'];
            $this->page['number'] = $this->page['number'] < 1 ? 1 : $this->page['number'];
        }

        if (is_null($field)) {
            return $this->page;
        } elseif (isset($this->page[$field])) {
            return $this->page[$field];
        }
        return null;
    }

    public function query(): array
    {
        if (! isset($this->query)) {
            $this->query = (array)app('request')->query();
        }

        return $this->query;
    }

    /**
     * @param string|null $field
     *
     * @return array|mixed|null
     */
    public function filter(string $field = null)
    {
        if (! isset($this->filter)) {
            $this->filter = (array)request()->query('filter');
        }

        if (is_null($field)) {
            return $this->filter;
        } elseif (isset($this->filter[$field])) {
            return $this->filter[$field];
        }

        return null;
    }
}
