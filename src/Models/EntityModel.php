<?php


namespace ITLeague\Microservice\Models;


use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use ITLeague\Microservice\Repositories\Interfaces\RepositoryInterface;
use ITLeague\Microservice\Traits\SerializesEntity;
use ITLeague\Microservice\Traits\ValidatableEntity;

abstract class EntityModel extends Model
{
    use SerializesEntity;
    use ValidatableEntity;


    /**
     * @var \ITLeague\Microservice\Repositories\Decorators\CachingRepository|string
     */
    protected static string $cachingRepositoryClass;

    /**
     * @var \ITLeague\Microservice\Repositories\Repository|string
     */
    protected static string $repositoryClass;

    /**
     * @var \Illuminate\Database\Eloquent\Factories\HasFactory|string
     */
    protected static string $translationClass;

    private const closureProperties = ['filters', 'sorts', 'rules'];
    private array $unfilled = [];

    protected array $eagerLoad = [];
    protected array $filters = [];
    protected array $sorts = [];


    /**
     * Сохраняет переданные, но незаполненные в текущей модели атрибуты в спец. свойство
     * Эти атрибуты могут использоваться, например, для сохранения релэйшенов или переводов через систему событий
     *
     * @param array $attributes
     *
     * @return $this
     */
    final public function fill(array $attributes): self
    {
        $result = parent::fill($attributes);
        $this->mergeUnfilled($attributes);
        return $result;
    }

    final public function mergeUnfilled(array $attributes): void
    {
        $unfilled = count($attributes) ? Arr::except($attributes, array_keys($this->attributes)) : [];
        $this->unfilled = array_merge($this->unfilled, $unfilled);
    }

    /**
     * @return array
     */
    final public function getEagerLoads(): array
    {
        return $this->eagerLoad;
    }

    /**
     * @return array
     */
    final public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param string $attribute
     *
     * @return mixed|null
     */
    final public function getUnfilledAttribute(string $attribute)
    {
        return Arr::get($this->unfilled, $attribute);
    }

    final public function getUnfilledAttributes(): array
    {
        return $this->unfilled;
    }

    public function getSorts(): array
    {
        return $this->sorts;
    }

    public static function repository(): RepositoryInterface
    {
        return new static::$cachingRepositoryClass(new static::$repositoryClass(new static()));
    }

    protected static function newFactory()
    {
        if (static::$translationClass) {
            return Factory::factoryForModel(get_called_class())->has(static::$translationClass::factory(), 'translation');
        }
    }
}
