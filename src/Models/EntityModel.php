<?php


namespace ITLeague\Microservice\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use ITLeague\Microservice\Repositories\Decorators\CachingRepository;
use ITLeague\Microservice\Repositories\Interfaces\RepositoryInterface;
use ITLeague\Microservice\Repositories\Repository;
use ITLeague\Microservice\Traits\Models\Serializable;
use ITLeague\Microservice\Traits\Models\Validatable;
use ITLeague\Microservice\Traits\Models\WithUnfilledAttributes;

abstract class EntityModel extends Model
{
    use Serializable;
    use Validatable;
    use WithUnfilledAttributes;

    protected static string|CachingRepository $cachingRepositoryClass;
    protected static string|Repository $repositoryClass;

    private const closureProperties = ['filters', 'sorts', 'rules'];

    protected array $eagerLoad = [];
    protected array $filters = [];
    protected array $sorts = [];

    /**
     * @return array
     */
    public function getEagerLoads(): array
    {
        return $this->eagerLoad;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getSorts(): array
    {
        return $this->sorts;
    }

    public static function repository(): ?RepositoryInterface
    {
        if (static::$cachingRepositoryClass && static::$repositoryClass) {
            return new static::$cachingRepositoryClass(new static::$repositoryClass(new static()));
        }

        return null;
    }
}
