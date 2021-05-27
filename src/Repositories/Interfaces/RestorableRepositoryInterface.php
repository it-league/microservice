<?php


namespace ITLeague\Microservice\Repositories\Interfaces;


interface RestorableRepositoryInterface extends RepositoryInterface
{
    public function restore(string|int $id): ?bool;
}
