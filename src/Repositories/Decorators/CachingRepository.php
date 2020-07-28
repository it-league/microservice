<?php


namespace itleague\microservice\Repositories\Decorators;


use itleague\microservice\Models\EntityModel;
use itleague\microservice\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Cache;

abstract class CachingRepository implements RepositoryInterface
{
    protected RepositoryInterface $repository;
    protected Cache\Repository $cache;

    protected int $ttl = 60;
    protected string $tag = '';

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->cache = app('cache.store');
    }

    public function show($id): EntityModel
    {
        $hash = md5("{$this->tag}:$id");

        return $this->cache->tags($this->tag)->remember("{$this->tag}:$hash", $this->ttl, function () use ($id) {
            return $this->repository->show($id);
        });
    }

    public function index(): Arrayable
    {
        $hash = md5(json_encode(app('query')->query()));

        return $this->cache->tags($this->tag)->remember("{$this->tag}:$hash", $this->ttl, function () {
            return $this->repository->index();
        });
    }

    public function store(array $fields): EntityModel
    {
        $model = $this->repository->store($fields);
        $this->flush();
        return $model;
    }

    public function update($id, array $fields): EntityModel
    {
        $model = $this->repository->update($id, $fields);
        $this->flush();
        return $model;
    }

    public function destroy($id): ?bool
    {
        $result = $this->repository->destroy($id);
        $this->flush();
        return $result;
    }

    public function restore($id): ?bool
    {
        $result = $this->repository->restore($id);
        $this->flush();
        return $result;
    }

    public function flush(): void
    {
        $this->cache->tags($this->tag)->flush();
    }
}
