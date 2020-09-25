<?php


namespace ITLeague\Microservice\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use ITLeague\Microservice\Casts\File;
use ITLeague\Microservice\Observers\FileAttributeObserver;
use ITLeague\Microservice\Traits\SerializableEntity;
use ITLeague\Microservice\Traits\ValidatableEntity;

abstract class EntityModel extends Model
{
    use SerializableEntity;
    use ValidatableEntity;

    private array $unfilled = [];

    protected array $eagerLoad = [];
    protected array $filters = [];
    protected array $files = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // add cast fo file attributes
        foreach ($this->getFiles() as $attribute => $settings) {
            $this->mergeCasts([$attribute => File::class]);
        }
    }

    protected static function booted()
    {
        parent::booted();

        // add observer for file attributes
        $instance = new static();
        if (count($instance->getFiles()) > 0) {
            $instance->registerObserver(FileAttributeObserver::class);
        }
    }

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
        $this->unfilled = count($attributes) ? Arr::except($attributes, array_keys($this->attributes)) : [];
        return $result;
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
     * @return array
     */
    final public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param string $attribute
     *
     * @return mixed|null
     */
    final public function getUnfilledAttribute(string $attribute)
    {
        return $this->unfilled[$attribute] ?? null;
    }

    final public function getUnfilledAttributes(): array
    {
        return $this->unfilled;
    }
}
