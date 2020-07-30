<?php


namespace itleague\microservice\Repositories;


use itleague\microservice\Models\EntityModel;
use itleague\microservice\Repositories\Interfaces\RepositoryInterface;
use DB;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Validator;

abstract class Repository implements RepositoryInterface
{
    /**
     * @var \itleague\microservice\Models\EntityModel|\Illuminate\Database\Eloquent\Builder
     */
    protected $model;
//    protected $with = [];
    protected array $filter;

    public function __construct(EntityModel $model)
    {
        $this->model = $model;
    }

//    /**
//     * @param Model|\Illuminate\Database\Eloquent\Collection $model
//     */
//    protected function addRelations($model): void
//    {
//        $relations = $this->with;
//        $relations = array_intersect($relations, app('query')->fields() ?? $relations);
//        $model->load($relations);
//    }

    protected function validate(array $data, string $method): array
    {
        /** @var EntityModel $model */
        $model = $this->model->getModel();
        return Validator::make($data, $model::rules($method))->validate();
    }

    protected function setFilters(): void
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
        $model = $this->model->findOrFail($id);
//        $this->addRelations($dataSet);

        return $model;
    }

    public function index(): Arrayable
    {
        $this->setFilters();

        if (request()->page('all') === true) {
            $collection = $this->model->get();
        } else {
            $collection = $this->model
                ->paginate(request()->page('size'), ['*'], 'page[number]', request()->page('number'))
                ->withPath(request()->fullUrlWithQuery(request()->except('page.number')));
        }

//        $this->addRelations($collection);

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

        return $this->model->onlyTrashed()->findOrFail($id)->restore();
    }
}
