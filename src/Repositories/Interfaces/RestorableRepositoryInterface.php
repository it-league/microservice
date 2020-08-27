<?php


namespace ITLeague\Microservice\Repositories\Interfaces;


interface RestorableRepositoryInterface
{
    public function restore($id): ?bool;
}
