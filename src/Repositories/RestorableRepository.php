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
