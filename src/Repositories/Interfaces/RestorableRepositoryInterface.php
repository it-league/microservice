<?php


namespace ITLeague\Microservice\Repositories\Interfaces;


interface RestorableRepositoryInterface extends RepositoryInterface
{
    public function restore($id): ?bool;
}
