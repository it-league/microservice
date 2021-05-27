<?php


namespace ITLeague\Microservice\Repositories;


use DB;
use ITLeague\Microservice\Repositories\Interfaces\RestorableRepositoryInterface;

abstract class RestorableRepository extends Repository implements RestorableRepositoryInterface
{
    /**
     * @throws \Throwable
     */
    public function restore(string|int $id): ?bool
    {
        return DB::transaction(fn() => $this->query->onlyTrashed()->findOrFail($id)->restore());
    }
}
