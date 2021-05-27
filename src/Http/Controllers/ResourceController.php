<?php

namespace ITLeague\Microservice\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use ITLeague\Microservice\Http\Interfaces\ResourceControllerInterface;
use ITLeague\Microservice\Repositories\Interfaces\RepositoryInterface;
use ITLeague\Microservice\Traits\ApiResponse;
use Laravel\Lumen\Routing\Controller as BaseController;

abstract class ResourceController extends BaseController implements ResourceControllerInterface
{
    use ApiResponse;

    protected RepositoryInterface $repository;
    protected JsonResource|string $resource;

    protected function respondEntity(string|int $id, int $code = Response::HTTP_OK): JsonResponse
    {
        $resource = new $this->resource($this->repository->show($id));
        return $this->respondResource($resource, $code);
    }

    protected function respondCollection(int $code = Response::HTTP_OK): JsonResponse
    {
        $collection = $this->resource::collection($this->repository->index());
        return $this->respondResource($collection, $code);
    }

    public function show(string|int $id): JsonResponse
    {
        return $this->respondEntity($id);
    }

    public function index(): JsonResponse
    {
        return $this->respondCollection();
    }

    public function store(): JsonResponse
    {
        $model = $this->repository->store(request()->post());
        return $this->respondEntity($model->getKey(), Response::HTTP_CREATED);
    }

    public function update(string|int $id): JsonResponse
    {
        $model = $this->repository->update($id, request()->post());
        return $this->respondEntity($model->getKey());
    }

    public function destroy(string|int $id): JsonResponse
    {
        $this->repository->destroy($id);
        return $this->respondNull();
    }

}
