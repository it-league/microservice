<?php

namespace ITLeague\Microservice\Http\Controllers;

use Illuminate\Http\JsonResponse;
use ITLeague\Microservice\Http\Interfaces\ResourceControllerInterface;
use ITLeague\Microservice\Repositories\Interfaces\RepositoryInterface;
use ITLeague\Microservice\Traits\ApiResponse;
use Laravel\Lumen\Routing\Controller as BaseController;

abstract class ResourceController extends BaseController implements ResourceControllerInterface
{
    use ApiResponse;

    protected RepositoryInterface $repository;

    /**
     * @var \Illuminate\Http\Resources\Json\JsonResource
     */
    protected $resource;

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

}
