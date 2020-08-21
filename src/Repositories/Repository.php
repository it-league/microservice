<?php


namespace itleague\microservice\Repositories;


use DB;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use itleague\microservice\Models\EntityModel;
use itleague\microservice\Repositories\Interfaces\RepositoryInterface;
use Validator;

abstract class Repository implements RepositoryInterface
{
    /**
     * @var \itleague\microservice\Models\EntityModel|\Illuminate\Database\Eloquent\Builder
     */
    protected $model;
    protected array $filter;
    protected array $sort;

    public function __construct(EntityModel $model)
    {
        $this->model = $model;
    }


    protected function validate(array $data, string $method): array
    {
        /** @var EntityModel $model */
        $model = $this->model->getModel();
        return Validator::make($data, $model::rules($method))->validate();
    }

    protected function setFilter(): void
    {
        $this->filter = $this->validate(request()->filter(), 'filter');

        $keyName = $this->model->getModel()->getKeyName();
        $keyValue = Arr::get($this->filter, $keyName);

        if (is_array($keyValue)) {
            $this->model = $this->model->whereIn($keyName, $keyValue);
        } elseif (! is_null($keyValue)) {
            $this->model = $this->model->whereKey($keyValue);
        }
    }

    public function show($id): EntityModel
    {
        $this->setExpectedRelations();
        return $this->model->findOrFail($id);
    }

    public function index(): Arrayable
    {
        $this->setFilter();
        $this->setSort();
        $this->setExpectedRelations();

        if (request()->page('all') === true) {
            $collection = $this->model->get();
        } else {
            $collection = $this->model
                ->paginate(request()->page('size'), ['*'], 'page[number]', request()->page('number'))
                ->withPath(request()->fullUrlWithQuery(request()->except('page.number')));
        }

        return $collection;

    }

    public function store(array $attributes): EntityModel
    {
        $attributes = $this->validate($attributes, 'store');

        DB::beginTransaction();

        try {

            $model = new $this->model($attributes);
            $model->save();

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            // TODO: действия при rollback

            throw $e;
        }

        return $model;
    }

    public function update($id, array $attributes): EntityModel
    {
        $attributes = $this->validate($attributes, 'update');

        DB::beginTransaction();

        try {

            $model = $this->show($id);
            $model->fill($attributes)->save();

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            // TODO: действия при rollback

            throw $e;
        }

        return $model;
    }

    /**
     * @param $id
     *
     * @return bool|null
     * @throws \Exception
     */
    public function destroy($id): ?bool
    {
        DB::beginTransaction();

        try {

            $model = $this->show($id);
            $result = $model->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            // TODO: действия при rollback

            throw $e;
        }

        return $result;
    }

    public function restore($id): ?bool
    {

        DB::beginTransaction();

        try {

            $result = $this->model->onlyTrashed()->findOrFail($id)->restore();
            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            // TODO: действия при rollback

            throw $e;
        }

        return $result;
    }

    // TODO: нужна проверка полей
    protected function setSort(): void
    {
//        $this->sort = $this->validate(request()->sort(), 'sort');

        $sort = collect(request()->sort())->mapWithKeys(function (string $field) {
            if ($field[0] === '-') {
                return [substr($field, 1) => 'desc'];
            } else {
                return [$field => 'asc'];
            }
        })->toArray();

        foreach ($sort as $field => $direction) {
            $this->model = $this->model->orderBy($field, $direction);
        }
    }

    protected function setExpectedRelations(): void
    {
        $with = $this->model->getModel()->getWith();
        $without = array_diff($with, request()->fields() ?? $with);
        $this->model = $this->model->without($without);
    }
}
