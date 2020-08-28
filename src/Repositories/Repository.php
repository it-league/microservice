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


    public function show($id): EntityModel
    {
        return $this->query->withRelations()->findOrFail($id);
    }

    public function index(): Arrayable
    {
        $query = $this->query->withRelations()->withSort()->withFilter();
        return request()->page('all') === true ? $query->get() : $query->getWithPage();
    }

    public function store(array $attributes): EntityModel
    {
        $attributes = $this->model->validate($attributes, 'store');

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
        $attributes = $this->model->validate($attributes, 'update');

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

            $result = $this->query->onlyTrashed()->findOrFail($id)->restore();
            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            // TODO: действия при rollback

            throw $e;
        }

        return $result;
    }
}
