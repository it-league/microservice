<?php


namespace ITLeague\Microservice\Repositories;


use DB;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use ITLeague\Microservice\Models\EntityModel;
use ITLeague\Microservice\Repositories\Interfaces\RepositoryInterface;

abstract class Repository implements RepositoryInterface
{
    protected Builder $query;
    protected EntityModel $model;

    public function __construct(EntityModel $model)
    {
        $this->model = $model;
        $this->query = $model->newQuery();
    }

    public function show($id): EntityModel
    {
        return $this->query->withRelations()->findOrFail($id);
    }

    public function index(): Arrayable
    {
        $query = $this->query->withRelations()->withSort()->withFilter();
        return request()->page('all') === true ? $query->get() : $query->getWithPage();
    }

    /**
     * @param array $attributes
     *
     * @return \ITLeague\Microservice\Models\EntityModel
     * @throws \Throwable
     */
    public function store(array $attributes): EntityModel
    {
        $attributes = $this->model->validateStore($attributes);
        return DB::transaction(fn() => (new $this->model($attributes))->save());
    }

    /**
     * @param $id
     * @param array $attributes
     *
     * @return \ITLeague\Microservice\Models\EntityModel
     * @throws \Throwable
     */
    public function update($id, array $attributes): EntityModel
    {
        $attributes = $this->model->validateUpdate($id, $attributes);
        return DB::transaction(fn() => $this->show($id)->update($attributes));
    }

    /**
     * @param $id
     *
     * @return bool|null
     * @throws \Throwable
     */
    public function destroy($id): ?bool
    {
        return DB::transaction(fn() => $this->show($id)->delete());
    }
}
