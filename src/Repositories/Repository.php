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

    public function __construct(protected EntityModel $model)
    {
        $this->query = $model->newQuery();
    }

    public function show(string|int $id): EntityModel
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
        return DB::transaction(
            function () use ($attributes) {
                $model = new $this->model($attributes);
                $model->save();
                return $model;
            }
        );
    }

    /**
     * @param $id
     * @param array $attributes
     *
     * @return \ITLeague\Microservice\Models\EntityModel
     * @throws \Throwable
     */
    public function update(string|int $id, array $attributes): EntityModel
    {
        $attributes = $this->model->validateUpdate($id, $attributes);

        return DB::transaction(
            function () use ($id, $attributes) {
                $model = $this->show($id);
                $model->update($attributes);
                return $model;
            }
        );
    }


    /**
     * @throws \Throwable
     */
    public function destroy(string|int $id): ?bool
    {
        return DB::transaction(fn() => $this->show($id)->delete());
    }
}
