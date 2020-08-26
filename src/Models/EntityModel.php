<?php


namespace itleague\microservice\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Validator;

abstract class EntityModel extends Model
{
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
    protected array $unfilled = [];

    protected static function booted()
    {
        parent::booted();
        static::setRules();
        static::$rules = [];
    }

    private static function setRules(): void
    {
        $rules = Validator::make(static::$rules, self::rulesValidator)->validate();
        static::$staticClassesRules[static::class] = $rules;
    }

    public static function rules(string $method): array
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
    public function fill(array $attributes): self
    {
        $result = parent::fill($attributes);
        $this->unfilled = count($attributes) ? Arr::except($attributes, array_keys($this->attributes)) : [];
        return $result;
    }

    /**
     * @return array
     */
    public function getEagerLoads(): array
    {
        return $this->eagerLoad;
    }

    public function validate(array $data, string $method): array
    {
        return Validator::make($data, $this::rules($method))->validate();
    }
}
