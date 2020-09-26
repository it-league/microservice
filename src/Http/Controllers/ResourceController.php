<?php

namespace ITLeague\Microservice\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use ITLeague\Microservice\Http\Interfaces\ResourceControllerInterface;
use ITLeague\Microservice\Repositories\Interfaces\RepositoryInterface;
use ITLeague\Microservice\Traits\ApiResponse;
use Laravel\Lumen\Routing\Controller as BaseController;

abstract class ResourceController extends BaseController implements ResourceControllerInterface
{
    use ApiResponse;

    protected RepositoryInterface $repository;

    /**
     * Resource class name
     */
    protected string $resource;

    public function show($id): JsonResponse
    {
        /** @var JsonResource $resource */
        $resource = new $this->resource($this->repository->show($id));
        return $this->respondResource($resource);
    }

    public function index(): JsonResponse
    {
        /** @var AnonymousResourceCollection $collection */
        $collection = $this->resource::collection($this->repository->index());
        return $this->respondResource($collection);
    }

    public function store(): JsonResponse
    {
        $model = $this->repository->store(request()->post());
        $model->load($model->getEagerLoads());
        return $this->respondResource(new $this->resource($model), 201);
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
