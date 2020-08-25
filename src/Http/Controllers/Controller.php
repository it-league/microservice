<?php

namespace itleague\microservice\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use itleague\microservice\Repositories\Interfaces\RepositoryInterface;
use itleague\microservice\Traits\ApiResponse;
use Laravel\Lumen\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use ApiResponse;

    /**
     * @var \itleague\microservice\Repositories\Interfaces\RepositoryInterface
     */
    protected RepositoryInterface $repository;
    /**
     * @var \Illuminate\Http\Resources\Json\JsonResource
     */
    protected JsonResource $resource;

    public function show($id): JsonResponse
    {
        $resource = new $this->resource($this->repository->show($id));
        return $this->respondResource($resource);
    }

    public function index(): JsonResponse
    {
        $collection = $this->resource::collection($this->repository->index());
        return $this->respondCollection($collection);
    }

    public function store(): JsonResponse
    {
        $model = $this->repository->store(request()->post());
        return $this->respondData([$model->getKeyName() => $model->getKey()], 201);
    }

    public function update($id): JsonResponse
    {
        $this->repository->update($id, request()->post());
        return $this->respondNull();
    }

    public function destroy($id): JsonResponse
    {
        $this->repository->destroy($id);
        return $this->respondNull();
    }

    public function restore($id): JsonResponse
    {
        $this->repository->restore($id);
        return $this->respondNull();
    }

}
