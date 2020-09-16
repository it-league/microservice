<?php


namespace ITLeague\Microservice\Http\Controllers;


use Illuminate\Http\JsonResponse;
use ITLeague\Microservice\Http\Interfaces\RestorableResourceControllerInterface;

abstract class RestorableResourceController extends ResourceController implements RestorableResourceControllerInterface
{
    public function restore($id): JsonResponse
    {
        $this->repository->restore($id);
        return $this->respondNull();
    }
}
