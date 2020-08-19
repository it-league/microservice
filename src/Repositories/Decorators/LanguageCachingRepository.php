<?php


namespace itleague\microservice\Repositories\Decorators;


use itleague\microservice\Models\Language;
use itleague\microservice\Repositories\Interfaces\LanguageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Cache;

class LanguageCachingRepository implements LanguageRepositoryInterface
{
    protected const ttl = 60 * 60 * 24 * 30;
    protected const tag = 'language';

    protected LanguageRepositoryInterface $repository;
    protected Cache\Repository $cache;

    public function __construct(LanguageRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->cache = app('cache.store');
    }

    public function default(): Language
    {
        return $this->cache->tags(static::tag)->remember(static::tag . ":default", static::ttl, function () {
            return $this->repository->default();
        });
    }

    public function all(): Collection
    {
        return $this->cache->tags(static::tag)->remember(static::tag . ":all", static::ttl, function () {
            return $this->repository->all();
        });
    }
}
