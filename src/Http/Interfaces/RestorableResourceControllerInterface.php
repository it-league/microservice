<?php


namespace ITLeague\Microservice\Http\Interfaces;


use Illuminate\Http\JsonResponse;

interface RestorableResourceControllerInterface extends ResourceControllerInterface
{
    public function restore(string|int $id): JsonResponse;
}
