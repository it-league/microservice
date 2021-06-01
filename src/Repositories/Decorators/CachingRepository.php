<?php


namespace ITLeague\Microservice\Repositories\Decorators;


use Illuminate\Cache;
use Illuminate\Contracts\Support\Arrayable;
use ITLeague\Microservice\Models\EntityModel;
use ITLeague\Microservice\Repositories\Interfaces\RepositoryInterface;
use ITLeague\Microservice\Repositories\Interfaces\RestorableRepositoryInterface;

abstract class CachingRepository implements RepositoryInterface, RestorableRepositoryInterface
{
    protected Cache\Repository $cache;

    private string $locale;
    protected bool $useLocale = true;
    protected bool $useUserRoles = false;
    protected bool $useUserId = false;

    protected int $ttl = 60;
    protected string $tag = '';

    protected array $relatedTags = [];

    public function __construct(protected RepositoryInterface $repository)
    {
        $this->cache = app('cache.store');
        $this->locale = app()->getLocale();
    }

    protected function hash(string $key): string
    {
        if ($this->useLocale) {
            $key .= ":$this->locale";
        }

        if ($this->useUserRoles && auth()->check()) {
            $roles = array_filter((array)auth()->user()->roles ?? ['user']);
            $roles = count($roles) > 0 ? implode(',', $roles) : 'user';
            $key .= ":$roles";
        }

        if ($this->useUserId && auth()->check()) {
            $id = auth()->id();
            $key .= ":$id";
        }

        return md5($key);
    }

    final public function show(string|int $id): EntityModel
    {
        return $this->cache->tags($this->tag)->remember(
            "$this->tag:{$this->hash("show:$id")}",
            $this->ttl,
            fn() => $this->repository->show($id)
        );
    }

    final public function index(): Arrayable
    {
        return $this->cache->tags($this->tag)->remember(
            "$this->tag:{$this->hash(md5('index:' . json_encode(request()->query())))}",
            $this->ttl,
            fn() => $this->repository->index()
        );
    }

    final public function store(array $attributes): EntityModel
    {
        $model = $this->repository->store($attributes);
        $this->flush();
        return $model;
    }

    final public function update(string|int $id, array $attributes): EntityModel
    {
        $model = $this->repository->update($id, $attributes);
        $this->flush();
        return $model;
    }

    final public function destroy(string|int $id): ?bool
    {
        $result = $this->repository->destroy($id);
        $this->flush();
        return $result;
    }

    final public function restore(string|int $id): ?bool
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
