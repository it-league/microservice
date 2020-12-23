<?php


namespace ITLeague\Microservice\Mixins;


use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

/** @mixin Builder */
class BuilderMixin
{

    public function withFilter()
    {
        return function () {
            /** @var \ITLeague\Microservice\Models\EntityModel $model */
            $model = $this->getModel();
            $filter = $model->validateFilter(request()->filter());

            $keyName = $model->getKeyName();

            if (! is_array($keyName) && ! is_null($keyName)) {
                $keyValue = Arr::get($filter, $keyName);

                if (is_array($keyValue)) {
                    $this->whereIn($keyName, $keyValue);
                } elseif (! is_null($keyValue)) {
                    $this->whereKey($keyValue);
                }
            }

            foreach ($model->getFilters() as $key => $closure) {
                if ($closure instanceof Closure && isset($filter[$key])) {
                    $closure($this, $filter[$key]);
                }
            }

            return $this;
        };
    }

    public function withSort()
    {
        return function () {

            $requestSort = request()->sort();

            if(!is_null($requestSort)) {
                $sort = collect($requestSort)->mapWithKeys(
                    fn(string $field) => (($field[0] ?? '') === '-') ? [substr($field, 1) => 'desc'] : [$field => 'asc']
                )->toArray();

                /** @var \ITLeague\Microservice\Models\EntityModel $model */
                $model = $this->getModel();
                $sort = Arr::only($sort, $model->validateSort(array_keys($sort)));

                foreach ($model->getSorts() as $key => $closure) {
                    if ($closure instanceof Closure && isset($sort[$key])) {
                        $closure($this, $sort[$key]);
                        unset($sort[$key]);
                    }
                }

                foreach ($sort as $field => $direction) {
                    $this->orderBy($field, $direction);
                }
            }

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
