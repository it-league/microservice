<?php


namespace ITLeague\Microservice\Repositories;


use DB;
use Exception;
use ITLeague\Microservice\Repositories\Interfaces\RestorableRepositoryInterface;

class RestorableRepository extends Repository implements RestorableRepositoryInterface
{
    /**
     * @param $id
     *
     * @return bool|null
     * @throws \Throwable
     */
    final public function restore($id): ?bool
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
