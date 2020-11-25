<?php


namespace ITLeague\Microservice\Repositories\Decorators;


use Illuminate\Cache;
use Illuminate\Contracts\Support\Arrayable;
use ITLeague\Microservice\Models\EntityModel;
use ITLeague\Microservice\Repositories\Interfaces\RepositoryInterface;

abstract class CachingRepository implements RepositoryInterface
{
    protected RepositoryInterface $repository;
    protected Cache\Repository $cache;

    private string $locale;
    protected bool $useLocale = true;

    protected int $ttl = 60;
    protected string $tag = '';

    protected array $relatedTags = [];

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->cache = app('cache.store');
        $this->locale = app()->getLocale();
    }

    protected function hash(string $key): string
    {
        return md5($key . ($this->useLocale ? ":{$this->locale}" : ''));
    }

    final public function show($id): EntityModel
    {
        return $this->cache->tags($this->tag)->remember(
            "{$this->tag}:{$this->hash("show:$id")}",
            $this->ttl,
            fn() => $this->repository->show($id)
        );
    }

    final public function index(): Arrayable
    {
        return $this->cache->tags($this->tag)->remember(
            "{$this->tag}:{$this->hash(md5('index:' . json_encode(request()->query())))}",
            $this->ttl,
            fn() => $this->repository->index()
        );
    }

    final public function store(array $fields): EntityModel
    {
        $model = $this->repository->store($fields);
        $this->flush();
        return $model;
    }

    final public function update($id, array $fields): EntityModel
    {
        $model = $this->repository->update($id, $fields);
        $this->flush();
        return $model;
    }

    final public function destroy($id): ?bool
    {
        $result = $this->repository->destroy($id);
        $this->flush();
        return $result;
    }

    final public function restore($id): ?bool
    {
        $result = $this->repository->restore($id);
        $this->flush();
        return $result;
    }

    final public function flush(): void
    {
        $tags = array_filter($this->relatedTags);
        array_push($tags, $this->tag);
        $this->cache->tags($tags)->flush();
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
