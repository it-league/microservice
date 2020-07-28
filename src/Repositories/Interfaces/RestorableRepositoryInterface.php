<?php


namespace itleague\microservice\Repositories\Interfaces;


interface RestorableRepositoryInterface
{
    public function restore($id): ?bool;
}
