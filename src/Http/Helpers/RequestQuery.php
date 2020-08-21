<?php


namespace itleague\microservice\Http\Helpers;


use Illuminate\Support\Arr;

class RequestQuery
{
    private ?array $fields;
    private array $page;
    private ?array $filter;
    private ?array $sort;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function instance()
    {
        static $self;
        if (! isset($self)) {
            $self = new static;
        }
        return $self;
    }

    public function sort(): ?array
    {
        if (! isset($this->sort)) {
            $sort = (string)request()->query('sort');

            if (strlen($sort) > 0) {
                $this->sort = array_map('trim', explode(',', $sort));
            } else {
                $this->sort = null;
            }
        }

        return $this->sort;
    }

    public function fields(): ?array
    {
        if (! isset($this->fields)) {
            $fields = (string)request()->query('fields');
            if (strlen($fields) > 0) {
                $this->fields = array_map('trim', explode(',', $fields));
            } else {
                $this->fields = null;
            }
        }

        return $this->fields;
    }

    /**
     * @param string|null $field
     *
     * @return array|string|null
     */
    public function page(?string $field = null)
    {
        if (! isset($this->page)) {
            $this->page = array_map('intval', (array)request()->query('page'));
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
        } else {
            return $this->page[$field];
        }
    }

    /**
     * @param string|null $field
     *
     * @return array|string|null
     */
    public function filter(?string $field = null)
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
