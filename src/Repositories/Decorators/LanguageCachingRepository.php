<?php


namespace ITLeague\Microservice\Repositories\Decorators;


use ITLeague\Microservice\Models\Language;
use ITLeague\Microservice\Repositories\Interfaces\LanguageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Cache;

final class LanguageCachingRepository implements LanguageRepositoryInterface
{
    private const ttl = 60 * 60 * 24 * 30;
    private const tag = 'language';

    private LanguageRepositoryInterface $repository;
    private Cache\Repository $cache;

    public function __construct(LanguageRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->cache = app('cache.store');
    }

    public function default(): Language
    {
        return $this->cache->tags(static::tag)->remember(self::tag . ":default", static::ttl, function () {
            return $this->repository->default();
        });
    }

    public function all(): Collection
    {
        return $this->cache->tags(static::tag)->remember(self::tag . ":all", static::ttl, function () {
            return $this->repository->all();
        });
    }
}
