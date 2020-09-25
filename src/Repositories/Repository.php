<?php


namespace ITLeague\Microservice\Repositories;


use DB;
use Exception;
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


    final public function show($id): EntityModel
    {
        return $this->query->withRelations()->findOrFail($id);
    }

    final public function index(): Arrayable
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
    final public function store(array $attributes): EntityModel
    {
        $attributes = $this->model->validateStore($attributes);

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

    /**
     * @param $id
     * @param array $attributes
     *
     * @return \ITLeague\Microservice\Models\EntityModel
     * @throws \Throwable
     */
    final public function update($id, array $attributes): EntityModel
    {
        $attributes = $this->model->validateUpdate($attributes);

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
     * @throws \Throwable
     */
    final public function destroy($id): ?bool
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
}
