<?php


namespace ITLeague\Microservice\Repositories;


use DB;
use Exception;
use ITLeague\Microservice\Repositories\Interfaces\RestorableRepositoryInterface;

abstract class RestorableRepository extends Repository implements RestorableRepositoryInterface
{
    /**
     * @param $id
     *
     * @return bool|null
     * @throws \Throwable
     */
    public function restore($id): ?bool
    {
        return DB::transaction(fn() => $this->query->onlyTrashed()->findOrFail($id)->restore());
    }
}
