<?php


namespace itleague\microservice\Mixins;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class BuilderMixin
{
    public function withFilter()
    {
        return function () {

            /** @var Builder $this */
            $model = $this->getModel();
            $filter = $model->validate(request()->filter(), 'filter');

            $keyName = $model->getKeyName();
            $keyValue = Arr::get($filter, $keyName);

            if (is_array($keyValue)) {
                $this->whereIn($keyName, $keyValue);
            } elseif (! is_null($keyValue)) {
                $this->whereKey($keyValue);
            }

            // TODO: добавить возможность доп. фильтров

            return $this;
        };
    }

    public function withSort()
    {
        return function () {

            // TODO: нужна проверка полей

            // $this->sort = $this->validate(request()->sort(), 'sort');

            $sort = collect(request()->sort())->mapWithKeys(function (string $field) {
                if ($field[0] === '-') {
                    return [substr($field, 1) => 'desc'];
                } else {
                    return [$field => 'asc'];
                }
            })->toArray();

            /** @var Builder $this */
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
            /** @var Builder $this */
            $with = $this->getModel()->getEagerLoads();
            $with = array_intersect($with, request()->fields() ?? $with);
            return $this->with($with);
        };
    }

    public function getWithPage()
    {
        return function () {

            /** @var Builder $this */
            return $this->paginate(request()->page('size'), ['*'], 'page[number]', request()->page('number'))
                ->withPath(request()->fullUrlWithQuery(request()->except('page.number')));
        };
    }
}
