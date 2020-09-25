<?php


namespace ITLeague\Microservice\Mixins;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

/** @mixin Builder */
class BuilderMixin
{

    public function withFilter()
    {
        return function () {
            $model = $this->getModel();
            $filter = $model->validate(request()->filter(), 'filter');

            $keyName = $model->getKeyName();
            $keyValue = Arr::get($filter, $keyName);

            if (is_array($keyValue)) {
                $this->whereIn($keyName, $keyValue);
            } elseif (! is_null($keyValue)) {
                $this->whereKey($keyValue);
            }

            foreach ($model->getFilters() as $key => $closure) {
                if ($closure instanceof \Closure && isset($filter[$key])) {
                    $closure($this, $filter[$key]);
                }
            }

            return $this;
        };
    }

    public function withSort()
    {
        return function () {
            // TODO: нужна проверка полей

            // $this->sort = $this->validate(request()->sort(), 'sort');

            $sort = collect(request()->sort())->mapWithKeys(
                function (string $field) {
                    if ($field[0] === '-') {
                        return [substr($field, 1) => 'desc'];
                    } else {
                        return [$field => 'asc'];
                    }
                }
            )->toArray();

            foreach ($sort as $field => $direction) {
                $this->orderBy($field, $direction);
            }

            // TODO: добавить возможность доп. сортировок

            return $this;
        };
    }

    public function withRelations()
    {
        return function () {
            $with = $this->getModel()->getEagerLoads();
            $with = array_intersect($with, request()->fields() ?? $with);
            return $this->with($with);
        };
    }

    public function getWithPage()
    {
        return function () {
            return $this->paginate(request()->page('size'), ['*'], 'page[number]', request()->page('number'))
                ->withPath(request()->fullUrlWithQuery(request()->except('page.number')));
        };
    }
}
