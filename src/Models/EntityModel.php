<?php


namespace ITLeague\Microservice\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use ITLeague\Microservice\Casts\File;
use ITLeague\Microservice\Observers\FileAttributeObserver;
use ITLeague\Microservice\Traits\SerializableEntity;
use Validator;

abstract class EntityModel extends Model
{
    use SerializableEntity;

    private array $unfilled = [];

    private static array $staticClassesRules;
    protected static array $rules;

    // TODO: попробовать доработать
    private const rulesValidator = [
        'store' => 'filled|array',
        'store.*' => 'required|array_or_string',
        'store.*.*' => 'required',

        'update' => 'filled|array',
        'update.*' => 'required|array_or_string',
        'update.*.*' => 'required',

        'filter' => 'filled|array',
        'filter.*' => 'required|array_or_string',
        'filter.*.*' => 'required',
    ];

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

        static::setRules();
        static::$rules = [];
    }

    private static function setRules(): void
    {
        $rules = Validator::make(static::$rules, self::rulesValidator)->validate();
        static::$staticClassesRules[static::class] = $rules;
    }

    final public static function rules(string $method): array
    {
        return Arr::get(static::$staticClassesRules, static::class . '.' . $method, []);
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

    final public function validate(array $data, string $method): array
    {
        return Validator::make($data, self::rules($method))->validate();
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
    final public function getUnfilled(string $attribute)
    {
        return $this->unfilled[$attribute] ?? null;
    }
}
